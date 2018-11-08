<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\MenuProvider;
use Illuminate\Http\Response;

class WordPressController extends Controller
{
    /**
     * Updates all cached menus
     *
     * @return Response
     */
    public function menu()
    {
        // Get all menu locations
        $menuLocations = MenuProvider::getMenuLocations(true);

        // Drop cached menus
        Cache::tags('menu-locations')->flush();

        // Get all menus, issuing a new cache
        foreach ($menuLocations as $menuName) {
            $menu = MenuProvider::location($menuName);
        }

        // Done
        return response()->json([
            'ok' => true,
            'data' => [
                'locations' => $menuLocations
            ]
        ], Response::HTTP_ACCEPTED);
    }
}
