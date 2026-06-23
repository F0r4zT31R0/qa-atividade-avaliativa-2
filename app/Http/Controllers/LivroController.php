<?php

namespace App\Http\Controllers;

use App\Models\Livro;
use App\Models\Autor;
use Illuminate\Http\Request;

class LivroController extends Controller
{
    // INDEX
    public function index()
    {
        $livros = Livro::all();
        return view('livros.index', compact('livros'));
    }

    // CREATE
    public function create()
    {
        $autores = Autor::all();
        return view('livros.create', compact('autores'));
    }

    // STORE
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'autor_id' => 'required|exists:autores,id',
        ]);

        Livro::create($request->all());

        return redirect()->route('livros.index')
                         ->with('success', 'Livro criado com sucesso!');
    }

    // SHOW
    public function show($id)
    {
        $livro = Livro::find($id);

        if (!$livro) {
            return abort(404, 'Livro não encontrado');
        }

        return view('livros.show', compact('livro'));
    }

    // EDIT
    public function edit($id)
    {
        $livro = Livro::find($id);

        if (!$livro) {
            return redirect()->route('livros.index')
                             ->withErrors('Livro não encontrado');
        }

        $autores = Autor::all();
        return view('livros.edit', compact('livro', 'autores'));
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $livro = Livro::find($id);

        if (!$livro) {
            return abort(404, 'Livro não encontrado');
        }

        $request->validate([
            'titulo' => 'required|string|max:255',
            'autor_id' => 'required|exists:autores,id',
        ]);

        $livro->update($request->all());

        return redirect()->route('livros.index')
                         ->with('success', 'Livro atualizado com sucesso!');
    }

    // DESTROY
    public function destroy($id)
    {
        $livro = Livro::find($id);

        if (!$livro) {
            return abort(404, 'Livro não encontrado');
        }

        $livro->delete();

        return redirect()->route('livros.index')
                         ->with('success', 'Livro excluído com sucesso!');
    }
}
