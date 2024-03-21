<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Employee;
use App\Models\Prestation;
use App\Models\Slot;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index()
    {
        $slots = Slot::with('appointment', 'employee')->get();
        $employees = Employee::all();
        $prestations = Prestation::all();

        $events = $slots->map(function ($slot) use ($employees) {
            $start_time_only = Carbon::parse($slot->start_time)->format('H:i:s');
            $end_time_only = Carbon::parse($slot->end_time)->format('H:i:s');

            $start = Carbon::parse($slot->date . ' ' . $start_time_only);
            $end = Carbon::parse($slot->date . ' ' . $end_time_only);

            $isSlotFree = is_null($slot->appointment);

            $employee = $employees->find($slot->employee_id);
            $employeeData = $employee ? [
                'id' => $employee->id,
                'name' => $employee->name,
                'color' => $employee->color,
            ] : null;

            return [
                'id' => $slot->id,
                'start' => $start->toDateTimeString(),
                'end' => $end->toDateTimeString(),
                'color' => $isSlotFree ? 'green' : 'red',
                'employee' => $employeeData,
            ];
        });

dump($events);
dump($prestations);

        return view('calendar', [
            'events' => $events,
            'employees' => $employees,
            'prestations' => $prestations,
        ]);
    }

    public function assign(Request $request)
    {
        // Créer un nouvel appointment
        $appointment = new Appointment();
        $appointment->user_id = $request->user_id;
        $appointment->slot_id = $request->slot_id;
        $appointment->save();

        return redirect()->back()->withSuccess('Créneau attribué avec succès.');
    }
}
