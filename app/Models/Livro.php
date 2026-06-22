<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Livro extends Model
{
    use HasFactory;
    protected $fillable = [
        'titulo',
        'isbn',
        'data_publicacao',
        'autor_id',
    ];
    protected $table = 'livros';
    public function autor()
    {
        return $this->belongsTo(Autor::class, 'autor_id');
    }
}
