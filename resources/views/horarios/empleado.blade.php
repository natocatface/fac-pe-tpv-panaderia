@extends('layouts.app')
@section('title', $usuario->name)
@section('content')
<h1 class="page-title">{{ $usuario->name }} · {{ number_format($horasMes, 2) }} h este mes</h1>
<div class="card"><table class="table"><tbody>
@foreach($fichajes as $f)
<tr><td>{{ $f->entrada->format('d/m H:i') }}</td><td>{{ $f->salida?->format('H:i') }}</td><td class="text-end">{{ number_format($f->horas, 2) }} h</td></tr>
@endforeach
</tbody></table></div>
@endsection
