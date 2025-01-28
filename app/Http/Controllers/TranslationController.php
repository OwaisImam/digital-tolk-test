<?php

namespace App\Http\Controllers;

use App\Models\Translation;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Translations",
 *     description="API Endpoints for managing translations"
 * )
 * 
 * @OA\Schema(
 *     schema="Translation",
 *     required={"locale", "group", "key", "value"},
 *     @OA\Property(property="locale", type="string", example="en"),
 *     @OA\Property(property="group", type="string", example="messages"),
 *     @OA\Property(property="key", type="string", example="welcome"),
 *     @OA\Property(property="value", type="string", example="Welcome to our site")
 * )
 */
class TranslationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/translations",
     *     summary="List all translations",
     *     tags={"Translations"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="tag",
     *         in="query",
     *         description="Filter translations by tag",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="key",
     *         in="query",
     *         description="Filter translations by key",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="content",
     *         in="query",
     *         description="Filter translations by content",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of translations",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Translation")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Translation::query();

        // Filter by tag
        if ($request->has('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('name', $request->tag);
            });
        }

        // Filter by key
        if ($request->has('key')) {
            $query->where('key', 'like', '%' . $request->key . '%');
        }

        // Filter by content
        if ($request->has('content')) {
            $query->where('value', 'like', '%' . $request->content . '%');
        }

        $translations = $query->with('tags')->paginate(10);

        return response()->json($translations);
    }

    /**
     * @OA\Post(
     *     path="/api/translations",
     *     summary="Create a new translation",
     *     tags={"Translations"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"group", "key", "locale", "value"},
     *             @OA\Property(property="group", type="string", example="web"),
     *             @OA\Property(property="key", type="string", example="welcome_message"),
     *             @OA\Property(property="locale", type="string", example="en"),
     *             @OA\Property(property="value", type="string", example="Welcome to our website!"),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="string", example="mobile"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Translation created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Translation")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group' => 'nullable|string',
            'key' => 'required|string',
            'locale' => 'required|string',
            'value' => 'required|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $translation = Translation::create([
            'group' => $request->group,
            'key' => $request->key,
            'locale' => $request->locale,
            'value' => $request->value,
        ]);

        // Attach tags if provided
        if ($request->has('tags')) {
            $tagIds = [];
            foreach ($request->tags as $tagName) {
                $tag = Tag::firstOrCreate(['name' => $tagName]);
                $tagIds[] = $tag->id;
            }
            $translation->tags()->sync($tagIds);
        }

        return response()->json($translation, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/translations/{id}",
     *     summary="Get a specific translation",
     *     tags={"Translations"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Translation ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Translation details",
     *         @OA\JsonContent(ref="#/components/schemas/Translation")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Translation not found"
     *     )
     * )
     */
    public function show($id)
    {
        $translation = Translation::with('tags')->find($id);

        if (!$translation) {
            return response()->json(['message' => 'Translation not found'], 404);
        }

        return response()->json($translation);
    }

    /**
     * @OA\Put(
     *     path="/api/translations/{id}",
     *     summary="Update a translation",
     *     tags={"Translations"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Translation ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"group", "key", "locale", "value"},
     *             @OA\Property(property="group", type="string", example="web"),
     *             @OA\Property(property="key", type="string", example="welcome_message"),
     *             @OA\Property(property="locale", type="string", example="en"),
     *             @OA\Property(property="value", type="string", example="Welcome to our website!"),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="string", example="mobile"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Translation updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Translation")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Translation not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $translation = Translation::find($id);

        if (!$translation) {
            return response()->json(['message' => 'Translation not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'group' => 'nullable|string',
            'key' => 'required|string',
            'locale' => 'required|string',
            'value' => 'required|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $translation->update([
            'group' => $request->group,
            'key' => $request->key,
            'locale' => $request->locale,
            'value' => $request->value,
        ]);

        // Sync tags if provided
        if ($request->has('tags')) {
            $tagIds = [];
            foreach ($request->tags as $tagName) {
                $tag = Tag::firstOrCreate(['name' => $tagName]);
                $tagIds[] = $tag->id;
            }
            $translation->tags()->sync($tagIds);
        }

        return response()->json($translation);
    }

    /**
     * @OA\Delete(
     *     path="/api/translations/{id}",
     *     summary="Delete a translation",
     *     tags={"Translations"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Translation ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Translation deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Translation not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        $translation = Translation::find($id);

        if (!$translation) {
            return response()->json(['message' => 'Translation not found'], 404);
        }
        DB::table('translation_tag')->where('translation_id', $id)->delete();
        $translation->delete();

        return response()->json("Translation deleted successfully", 204);
    }
}