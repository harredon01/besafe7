<?php

use Illuminate\Database\Seeder;
use App\Services\MerchantImport;

class TranslationsSeeder extends Seeder {

    /**
     * The edit alerts implementation.
     *
     */
    protected $merchantImport;

    public function __construct(MerchantImport $merchantImport) {

        $this->merchantImport = $merchantImport;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        DB::table('translations')->delete();
        $this->merchantImport->importTranslations("translations.xlsx");
        $this->command->info('Translations seeded');
    }

}
