@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800  leading-tight">
        {{ __('Gestion du salon') }}
    </h2>
@endsection

@section('content')
    <div class="container">
        <h1>Paramètres du Salon</h1>
        <form method="POST" action="{{ route('salon.update') }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label">Nom du Salon</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $setting->name) }}" placeholder="Nom du Salon">
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">Adresse</label>
                <input type="text" class="form-control" id="address" name="address" value="{{ old('address', $setting->address) }}" placeholder="Adresse">
            </div>

            <div class="mb-3">
                <label for="slot_duration" class="form-label">Durée d'un Créneau (en minutes)</label>
                <input type="number" class="form-control" id="slot_duration" name="slot_duration" value="{{ old('slot_duration', $setting->slot_duration) }}" placeholder="Durée d'un Créneau (en minutes)">
            </div>

            @php
                $openDays = $setting->open_days ? json_decode($setting->open_days, true) : [];
            @endphp

            @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                <div class="mb-3">
                    <label class="form-label">{{ ucfirst($day) }}</label>
                    <div>
                        <input type="time" name="open_days[{{ $day }}][open]" value="{{ old('open_days.'.$day.'.open', $openDays[$day]['open'] ?? '') }}" placeholder="{{ ucfirst($day) }} Heure d'Ouverture">
                        <input type="time" name="open_days[{{ $day }}][close]" value="{{ old('open_days.'.$day.'.close', $openDays[$day]['close'] ?? '') }}" placeholder="{{ ucfirst($day) }} Heure de Fermeture">
                        <input type="time" name="open_days[{{ $day }}][break_start]" value="{{ old('open_days.'.$day.'.break_start', $openDays[$day]['break_start'] ?? '') }}" placeholder="{{ ucfirst($day) }} Début de la Pause">
                        <input type="time" name="open_days[{{ $day }}][break_end]" value="{{ old('open_days.'.$day.'.break_end', $openDays[$day]['break_end'] ?? '') }}" placeholder="{{ ucfirst($day) }} Fin de la Pause">

                    </div>
                </div>
            @endforeach

            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </form>
    </div>
@endsection
