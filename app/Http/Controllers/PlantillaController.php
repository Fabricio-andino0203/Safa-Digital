<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MensajePlantilla;

class PlantillaController extends Controller
{
    public function index()
    {
        $plantillas = MensajePlantilla::all();
        return view('configuracion.plantillas.index', compact('plantillas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'    => 'required|string|max:255',
            'evento'    => 'required|string|max:255|unique:mensaje_plantillas',
            'contenido' => 'required|string',
            'activa'    => 'boolean'
        ]);

        MensajePlantilla::create($validated);

        return redirect()->back()->with('success', 'Plantilla creada con éxito.');
    }

    public function update(Request $request, $id)
    {
        $plantilla = MensajePlantilla::findOrFail($id);

        $validated = $request->validate([
            'nombre'    => 'required|string|max:255',
            'evento'    => 'required|string|max:255|unique:mensaje_plantillas,evento,' . $id,
            'contenido' => 'required|string',
            'activa'    => 'boolean'
        ]);

        $validated['activa'] = $request->has('activa');

        $plantilla->update($validated);

        return redirect()->back()->with('success', 'Plantilla actualizada con éxito.');
    }

    public function destroy($id)
    {
        MensajePlantilla::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Plantilla eliminada con éxito.');
    }
}
