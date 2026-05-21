<?php

test('root redirects guests to the login page', function () {
    $this->get('/')->assertRedirect(route('login'));
});