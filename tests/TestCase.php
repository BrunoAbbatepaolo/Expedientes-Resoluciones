<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    // RefreshDatabase, sin esto, solo migra/restaura la conexión default entre
    // tests (ver Illuminate\Foundation\Testing\RefreshDatabase::connectionsToTransact()).
    // mysql_admin/mysql_legui quedan fuera de esa lista, así que su esquema
    // sqlite in-memory (ver phpunit.xml) se perdía a partir del segundo test:
    // se migraban una sola vez, pero cada test reconstruye el contenedor de la
    // app y con él una conexión ":memory:" nueva y vacía para esas conexiones.
    protected $connectionsToTransact = ['sqlite', 'mysql_admin', 'mysql_legui'];
}
