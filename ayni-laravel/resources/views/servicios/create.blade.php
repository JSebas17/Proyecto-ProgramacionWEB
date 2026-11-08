{{-- Publicar servicio nuevo: reutiliza el formulario compartido. --}}
@extends('layouts.app')
@section('titulo', 'Publicar servicio')
@section('contenido')
  @include('servicios.form', ['servicio' => null, 'accion' => route('servicios.store')])
@endsection