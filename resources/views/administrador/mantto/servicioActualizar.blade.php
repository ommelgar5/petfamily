@extends('layouts.material')

@section('menuLateral')
    @include('administrador.menuLateral')
@endsection

@section('contenido')
    <form action="{{ route('servicio.actualizar', ['cod_tipo_servicio' => $servicio->cod_tipo_servicio]) }}" method="POST" id="formServicio">
        @csrf
        @method('PUT')
        @include('partials.servicio')
        <button type="submit" class="btn btn-primary mr-5" id="guardar">Guardar</button>
        <a class="btn btn-default" href="{{ route('servicios') }}"> Cancelar</a>
    </form>

@endsection

@section('jsExtra')
    <script src="{{ asset('js/validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/validation/jquery.validate.additional-methods.min.js') }}"></script>
    <script src="{{ asset('js/validation/messages_es.js') }}"></script>
    <script>
        $(document).ready(function () {

            $("#guardar").click(function (event) {
                jQuery.validator.addMethod("formato", function (value, element) {
                    return this.optional(element) || /^[a-zA-Z áéíóúñÁÉÍÓÚÑ \s]+$/.test(value);
                }, "Carácter no valido en el campo");

                $("#formServicio").validate({
                    rules: {
                        servicio: {
                            required: true,
                            maxlength: 50,
                            formato: true
                        },
                    },
                });
            });

        });
    </script>

    @if(session()->has('error'))
        <script>
            Command: toastr["error"]("{{ session()->get('error') }}", "¡Error!")
            @include('partials.message')
        </script>
    @elseif(session()->has('info'))
        <script>
            Command: toastr["info"]("{{ session()->get('info') }}", "¡Información!")
            @include('partials.message')
        </script>
    @endif
@endsection
