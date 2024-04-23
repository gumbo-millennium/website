# Telegram Bot

The Gumbo Bot (gumbot) has a few commands to allow access to the website from Telegram groups.

Some features include:

-   Viewing cameras
-   Viewing the activities
-   Sending dumb quotes

## `/quote` and `/wjd`

The QuoteCommand takes care of the quotes. It does a few checks:

1. The quote (the part after `/quote`) is not empty
2. The user is logged in _or_ hasn't posted that day
3. It stores the quote with the message ID
4. It replies that it's been saved, and stores that ID in the reply_id
5. It optionally advertises that the user must log in.

The `message_id` and `reply_id` are used to support reactions. Users can reply on
posts, and we only want to store _one_ reply. When a reaction is sent, it's tied
to the Quote ID, which is found using either the message_id or reply_id.

This is just a safety to ensure users cannot fake the system with double votes, and it
cleans up nicely concerning double entries.

### How reactions are stored

Telegram sends a reaction, which is an ID of a custom emoji or a regular emoji. I don't
care about the contents of the reaction, so I'll just map the reaction list to an array
of emoji or "custom" and join those. Empty strings get turned to null.

A prune job will take care of removing empty reactions, just to prevent racing and to
keep the "handle reaction" job clean.
