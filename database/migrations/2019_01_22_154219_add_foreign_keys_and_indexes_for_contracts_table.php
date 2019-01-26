<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeysAndIndexesForContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->foreign('seller_company_id')->references('id')->on('companies');
            $table->index('seller_company_id');

            $table->foreign('client_company_id')->references('id')->on('companies');
            $table->index('client_company_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign('contracts_seller_company_id_foreign');
            $table->dropIndex('contracts_seller_company_id_index');

            $table->dropForeign('contracts_client_company_id_foreign');
            $table->dropIndex('contracts_client_company_id_index');
        });
    }
}
