<?php

use App\Models\AccountMapping;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        AccountMapping::getCoaId('input_tax');
        AccountMapping::getCoaId('output_tax');
    }

    public function down(): void
    {
        AccountMapping::query()->whereIn('mapping_key', ['input_tax', 'output_tax'])->delete();
    }
};
