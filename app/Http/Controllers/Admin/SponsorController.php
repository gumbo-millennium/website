<?php

namespace App\Http\Controllers\Admin;

use App\Sponsor;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Handles creating sponsors
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class SponsorController extends Controller
{
    /**
     * Returns the query to work with everywhere
     *
     * @param bool $onlyAvailable
     * @return Collection
     */
    public function getQuery(bool $onlyAvailable = null) : Collection
    {
        $query = Sponsor::latest();
        if ($onlyAvailable) {
            $query = $query->available();
        }
        return $query;
    }

    /**
     * Figures out the page for the given sponsor
     *
     * @param Sponsor $sponsor
     * @param bool $onlyAvailable
     * @return int|null
     */
    public function getPageForSponsor(Sponsor $sponsor, bool $onlyAvailable = null) : ?int
    {
        // Return null if not found
        if (!$sponsor->exists()) {
            return null;
        }

        // Find the item

        $list = $query->where('created_at', '<', $sponsor->created_at)->count();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Check if the user only wants to show the available items
        if ($request->get('available')) {
            $sponsors = Sponsor::available()->paginate();
        } else {
            $sponsors = Sponsor::paginate();
        }

        // Show sponsors
        return view('admin.sponsors.index', [
            'sponsors' => $sponsors,
            'available' => $request->get('available'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.sponsors.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SponsorChangeRequest $request)
    {
        // TODO
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Sponsor  $sponsor
     * @return \Illuminate\Http\Response
     */
    public function show(Sponsor $sponsor)
    {
        return view('admin.sponsors.create', [
            'sponsor' => $sponsor
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Sponsor  $sponsor
     * @return \Illuminate\Http\Response
     */
    public function edit(Sponsor $sponsor)
    {
        return view('admin.sponsors.update', [
            'sponsor' => $sponsor
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Sponsor  $sponsor
     * @return \Illuminate\Http\Response
     */
    public function update(SponsorChangeRequest $request, Sponsor $sponsor)
    {
        // TODO
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Sponsor  $sponsor
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sponsor $sponsor)
    {
        $sponsor->delete();

        return redirect()->back()->with([
            'status' => "De {$sponsor->name} is verwijderd."
        ]);
    }
}
