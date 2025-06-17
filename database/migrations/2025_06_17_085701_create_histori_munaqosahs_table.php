<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoriMunaqosahsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('histori_munaqosahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_munaqosah')
                  ->nullable() // Bisa null jika histori dari munaqosah yang sudah dihapus
                  ->constrained('munaqosahs')
                  ->onDelete('cascade'); // Jika munaqosah dihapus, historinya ikut terhapus
            $table->text('perubahan'); // Deskripsi perubahan
            $table->foreignId('dilakukan_oleh')
                  ->nullable() // User yang melakukan perubahan (bisa null jika user dihapus)
                  ->constrained('users') // Foreign key ke tabel 'users' (dari autentikasi)
                  ->onDelete('set null');
            $table->timestamp('created_at')->useCurrent(); // Hanya created_at, bukan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('histori_munaqosahs');
    }
}