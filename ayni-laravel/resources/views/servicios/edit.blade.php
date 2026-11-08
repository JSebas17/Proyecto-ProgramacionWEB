{{-- Editar servicio existente: reutiliza el formulario compartido. --}}
@extends('layouts.app')
@section('titulo', 'Editar servicio')
@section('contenido')
  @include('servicios.form', ['accion' => route('servicios.update', $servicio)])
@endsection