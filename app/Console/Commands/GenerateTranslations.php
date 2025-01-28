<?php

namespace App\Console\Commands;

use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class GenerateTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:generate {count=1}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate test translations';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $count = $this->argument('count');
        $batchSize = 1000;
        $tags = Tag::factory()->count(3)->create();
        for ($i = 0; $i < $count; $i += $batchSize) {
            $batch = [];
            for ($j = 0; $j < $batchSize; $j++) {
                $batch[] = [
                    'group' => rand(0, 1) ? 'web' : 'mobile',
                    'key' => 'key_'.Str::random(10),
                    'locale' => ['en', 'fr', 'es'][array_rand(['en', 'fr', 'es'])],
                    'value' => 'Value '.Str::random(20),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            Translation::insert($batch);
            $lastId = Translation::latest()->first()->id;
            $translations = Translation::whereBetween('id', [$lastId - $batchSize + 1, $lastId])->get();

            $pivotData = [];
            foreach ($translations as $t) {
                $selectedTags = $tags->random(rand(0, 3))->pluck('id');
                foreach ($selectedTags as $tagId) {
                    $pivotData[] = ['translation_id' => $t->id, 'tag_id' => $tagId];
                }
            }

            DB::table('translation_tag')->insert($pivotData);
        }
    }
}
