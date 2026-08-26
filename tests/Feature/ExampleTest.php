<?php

it('redirects the root page to login', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});
