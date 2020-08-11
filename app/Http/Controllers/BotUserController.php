<?php

namespace App\Http\Controllers;

use App\Models\BotUserLink;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Flash\Flash;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BotUserController extends Controller
{
    /**
     * Require signed urls when linking, and logged in users everywhere
     */
    public function __construct() {
        $this->middleware('auth');
        $this->middleware('signed')->only('link');
    }

    /**
     * Validates if the BotUserLink can be linked
     * @param BotUserLink $link
     * @return bool
     */
    private function validateLink(BotUserLink $link): bool
    {
        if ($link->user_id !== null) {
            \flash('Deze account is al gekoppeld aan een gebruiker', 'warning');
            return false;
        }

        return true;
    }

    /**
     * Validates if the BotUserLink can be unlinked (only if request user and link user
     * match)
     * @param Request $request
     * @param BotUserLink $link
     * @return bool
     */
    private function validateUnlink(Request $request, BotUserLink $link): bool
    {
        if ($link->user_id === null) {
            \flash('Deze account is niet gekoppeld aan een gebruiker', 'warning');
            return false;
        }

        if ($link->user_id !== $request->user()->id) {
            \flash('Deze account is gekoppeld aan een andere gebruiker', 'warning');
            return false;
        }

        return true;
    }

    /**
     * Home page, shows active links
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        // Just get all linked bot users
        $links = BotUserLink::whereUserId($request)
            ->get();

        // Render view
        return \response()
            ->view('account.bot-links.index', compact('links'))
            ->setPrivate();
    }

    /**
     * Returns the view to connect the BotUser to the User
     * @param BotUserLink $link
     * @return RedirectResponse|Response
     */
    public function showLink(BotUserLink $link)
    {
        // Check if linkable
        if (!$this->validateLink($link)) {
            return \response()
                ->redirectToRoute('bot-links.index')
                ->setPrivate();
        }

        // Show link
        return \response()
            ->view('account.bot-links.link', compact('link'));
    }

    /**
     * Returns the view to connect the BotUser to the User
     * @param BotUserLink $link
     * @return RedirectResponse|Response
     */
    public function link(Request $request, BotUserLink $link)
    {
        // Check if linkable
        if (!$this->validateLink($link)) {
            return \response()
                ->redirectToRoute('bot-links.index')
                ->setPrivate();
        }

        // Get user
        $user = $request->user();

        // Update link
        $link->user()->associate($user);
        $link->save();

        // Inform user
        \flash(
            "De {$link->driver} \"{$link->name}\" is nu gekoppeld aan jouw account",
            'success'
        );

        // Redirect back
        return \response()
            ->redirectToRoute('bot-links.index')
            ->setPrivate();
    }

    /**
     * Returns the view to disconnect the BotUser from the User
     * @param Request $request
     * @param BotUserLink $link
     * @return RedirectResponse|Response
     */
    public function showUnlink(Request $request, BotUserLink $link)
    {
        // Check if linkable
        if (!$this->validateUnlink($request, $link)) {
            return \response()
                ->redirectToRoute('bot-links.index')
                ->setPrivate();
        }

        // Show link
        return \response()
            ->view('account.bot-links.unlink', compact('link'));
    }

    /**
     * Deletes the bot-user ←→ user link
     * @param Request $request
     * @param BotUserLink $link
     * @return RedirectResponse|Response
     */
    public function unlink(Request $request, BotUserLink $link)
    {
        // Check if linkable
        if (!$this->validateUnlink($request, $link)) {
            return \response()
                ->redirectToRoute('bot-links.index')
                ->setPrivate();
        }

        // Just drop the link
        $link->delete();

        // Inform user
        \flash(
            "De {$link->driver} \"{$link->name}\" is ontkoppeld. Er zijn geen overige gegevens verwijderd.",
            'success'
        );

        // Redirect back
        return \response()
            ->redirectToRoute('bot-links.index')
            ->setPrivate();
    }
}
