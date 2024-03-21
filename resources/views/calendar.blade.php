@extends('layouts.app')

@section('content')

    <style>
        .filter-wrapper {
            border: 1px solid #ccc;
            background-color: #fff;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .form-check-label {
            display: flex;
            align-items: center;
        }

        .color-indicator {
            height: 15px;
            width: 15px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
    </style>

    <div class="d-flex">
        <div class="form-group filter-wrapper">
            <label>Filtrer par employé :</label>
            <div>
                @foreach($employees as $employee)
                    <div class="form-check">
                        <input class="form-check-input employeeFilter" type="checkbox" value="{{ $employee->id }}" id="employee{{ $employee->id }}">
                        <label class="form-check-label" for="employee{{ $employee->id }}">
                            {{ $employee->name }}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="form-group filter-wrapper">
            <label for="prestationSelect">Choisir une Prestation :</label>
            <select name="prestation_id" id="prestationSelect" class="form-control">
                <option value="">Toutes les prestations</option>
                @foreach($prestations as $prestation)
                    <option value="{{ $prestation->id }}" data-duree="{{ $prestation->temps }}">{{ $prestation->nom }} ({{ $prestation->temps }} minutes)</option>
                @endforeach
            </select>
        </div>

        <div class="container">
            <div id="calendar">ok</div>
        </div>
    </div>

    <!-- Modal Structure (exemple avec Bootstrap) -->
    <div class="modal" id="appointmentModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form action="{{ route('calendar.assign') }}" method="POST">
                @csrf
                @method('POST')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Attribuer Créneau</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="slot_id" id="slotId">
                        <div class="form-group">
                            <label for="userId">Choisir un Client</label>
                            <select name="user_id" id="userId" class="form-control">
                                @foreach(App\Models\User::all() as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                        <button class="btn btn-primary">Attribuer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection
@push('scripts')
    <script>
        var events = @json($events);
    </script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                slotMinTime: '08:00:00',
                slotMaxTime: '20:00:00',
                slotLabelInterval: '01:00',
                eventClick: function(info) {
                    document.getElementById('slotId').value = info.event.id;
                    $('#appointmentModal').modal('show');
                    document.querySelector('.close').addEventListener('click', function() {
                        $('#appointmentModal').modal('hide');
                    });
                },
                events: events
            });
            calendar.render();

            const employeeFilters = document.querySelectorAll('.employeeFilter');

            employeeFilters.forEach(filter => {
                filter.addEventListener('change', updateCalendarEvents);
            });

            const prestationSelect = document.getElementById('prestationSelect');
            prestationSelect.addEventListener('change', updateCalendarEvents);

            function updateCalendarEvents() {
                const selectedEmployeeIds = Array.from(employeeFilters)
                    .filter(input => input.checked)
                    .map(input => parseInt(input.value));

                // Filtrer les événements par employé sélectionné
                let filteredByEmployee = events.filter(event =>
                    selectedEmployeeIds.length === 0 || selectedEmployeeIds.includes(event.employee.id)
                );

                // Convertir la durée de la prestation sélectionnée en nombre de créneaux nécessaires
                const prestationDurationMinutes = parseInt(prestationSelect.options[prestationSelect.selectedIndex]?.dataset.duree || 0);
                const slotsNeeded = Math.ceil(prestationDurationMinutes / 60); // Chaque créneau dure 60 minutes

                let availableSlots = [];

                // Vérifier chaque créneau pour voir s'il démarre une série de créneaux consécutifs suffisants
                for (let i = 0; i < filteredByEmployee.length; i++) {
                    let series = [filteredByEmployee[i]]; // Commencer une nouvelle série avec le créneau actuel
                    let seriesEnd = new Date(filteredByEmployee[i].end).getTime();

                    for (let j = i + 1; j < filteredByEmployee.length && series.length < slotsNeeded; j++) {
                        let nextStart = new Date(filteredByEmployee[j].start).getTime();
                        let nextEnd = new Date(filteredByEmployee[j].end).getTime();

                        // Vérifier si le créneau suivant est consécutif et ajouter à la série
                        if (seriesEnd === nextStart) {
                            series.push(filteredByEmployee[j]);
                            seriesEnd = nextEnd;
                        }
                    }

                    // Si la série de créneaux est suffisante pour la prestation, ajouter le premier créneau de la série
                    if (series.length >= slotsNeeded) {
                        availableSlots.push(filteredByEmployee[i]);
                        // Ne pas sauter les créneaux déjà couverts car ils peuvent démarrer une nouvelle série valide
                    }
                }

                // Mettre à jour le calendrier avec les créneaux disponibles
                calendar.removeAllEvents();
                calendar.addEventSource(availableSlots);
                calendar.render();
            }



        });
    </script>
@endpush
