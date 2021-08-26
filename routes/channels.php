<?php

declare(strict_types=1);

Broadcast::channel('App.User.{id}', static fn ($user, $id) => (int) $user->id === (int) $id);
