
@if (session('message'))
<div class="alert"></div>
    {{ session('message') }}
</div>
@endif

<form action="{{ route('bibliotecas.update', ['id' => $biblioteca->id]) }}" method="POST">
    @csrf
    @method('PUT')
    <!-- Form fields for editing biblioteca -->

    <label for="nome">Nome:</label>
    <input type="text" id="nome" name="nome" value="{{ $biblioteca->nome }}" required>

    <label for="endereco">Endereço:</label>
    <input type="text" id="endereco" name="endereco" value="{{ $biblioteca->endereco }}" required>

    <label for="telefone">Telefone:</label>
    <input type="text" id="telefone" name="telefone" value="{{ $biblioteca->telefone }}" required>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" value="{{ $biblioteca->email }}" required>

    <button type="submit">Atualizar Biblioteca</button>

</form>