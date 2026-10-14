<?php

it('manda a quien no inició sesión directo al login', function () {
    $this->get('/')->assertRedirect(route('login'));
});
