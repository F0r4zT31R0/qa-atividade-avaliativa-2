@extends('layouts.app')

@section('content')
    <h1>{{ $livro->titulo }}</h1>
    <p>Autor: {{ $livro->autor->nome }}</p>
    <p>ISBN: {{ $livro->isbn }}</p>
    <p>Data de Publicação: {{ $livro->data_publicacao }}</p>

    <a href="{{ route('livros.index') }}">Voltar</a>
@endsection
