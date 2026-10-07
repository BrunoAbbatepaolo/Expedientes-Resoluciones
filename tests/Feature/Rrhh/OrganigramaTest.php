<?php

use App\Models\RrhhOficinaPadre;
use App\Services\Rrhh\Organigrama;
use Tests\Support\MitivFalso;

beforeEach(function () {
    MitivFalso::crear();
    $this->org = app(Organigrama::class);
    $this->area = MitivFalso::oficinaRrhh('SECRETARIA GENERAL');
    $this->dpto = MitivFalso::oficinaRrhh('DPTO. DESPACHO');
    $this->secc = MitivFalso::oficinaRrhh('SECC. ARCHIVO');
    $this->otra = MitivFalso::oficinaRrhh('DPTO. COMPUTOS');
    $this->org->asignarPadre($this->dpto, $this->area);
    $this->org->asignarPadre($this->secc, $this->dpto);
});

test('los descendientes incluyen todos los niveles y la propia oficina', function () {
    expect($this->org->descendientes([$this->area]))
        ->toEqualCanonicalizing([$this->area, $this->dpto, $this->secc])
        ->and($this->org->descendientes([$this->dpto]))->toEqualCanonicalizing([$this->dpto, $this->secc])
        ->and($this->org->descendientes([$this->otra]))->toBe([$this->otra]);
});

test('un ciclo cargado a mano no cuelga el recorrido', function () {
    RrhhOficinaPadre::updateOrCreate(['mitiv_oficina_id' => $this->area], ['padre_mitiv_oficina_id' => $this->secc]);

    expect($this->org->descendientes([$this->area]))->toEqualCanonicalizing([$this->area, $this->dpto, $this->secc]);
});

test('no se puede depender de sí misma ni formar un ciclo', function () {
    expect(fn () => $this->org->asignarPadre($this->area, $this->area))->toThrow(InvalidArgumentException::class)
        ->and(fn () => $this->org->asignarPadre($this->area, $this->secc))->toThrow(InvalidArgumentException::class);
});

test('quitar el superior deja la oficina como raíz', function () {
    $this->org->asignarPadre($this->dpto, null);

    expect($this->org->padres()[$this->dpto] ?? null)->toBeNull()
        ->and($this->org->descendientes([$this->area]))->toBe([$this->area]);
});

test('el árbol ordena por dependencia con su nivel', function () {
    $arbol = collect($this->org->arbol())->map(fn ($n) => [$n['oficina']->id, $n['nivel']])->all();

    expect($arbol)->toBe([
        [$this->otra, 0],
        [$this->area, 0],
        [$this->dpto, 1],
        [$this->secc, 2],
    ]);
});
