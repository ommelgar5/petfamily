
@extends('layouts.material')

@section('menuLateral')
    @include('secretaria.menuLateral')
@endsection

@section('contenido')
    <div class="row">
        <div class="col-md-12">
            <div class="card">

                <div class="card-header card-header-info">
                    <h4 class="card-title ">Cambio de contraseña</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('cambio.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        @include('partials.password');
                        <button type="submit" class="btn btn-info">Guardar</button>
                        <a href="{{ route('secretaria.dashboard') }}" class="btn btn-default">Cancelar</a>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('jsExtra')
    @if(session()->has('success'))
        <script>
            Command: toastr["success"]("{{ session()->get('success') }}", "¡Éxito!")
            @include('partials.message')
        </script>
    @elseif(session()->has('error'))
        <script>
            Command: toastr["error"]("{{ session()->get('error') }}", "¡Error!")
            @include('partials.message')
        </script>
    @endif

@endsection

