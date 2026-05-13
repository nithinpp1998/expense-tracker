<?php

declare(strict_types=1);

it('redirects unauthenticated visitors away from dashboard', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});
