# Contribution guide

So, you'd like to help with the Gumbo Millennium website? That's absolutely
AWESOME 🎉!

This guide describes how to get started with developing the website. It should
be pretty hacktoberfest-proof (and, thusly, easy to use), so please create a
documentation issue if you can't figure out some parts of this guide.

## Getting Started

This project is fully available on GitHub Codespaces, just click the "Code"
button in the top right, click on the Codespaces tab and start your
environment.  It should take about a minute or so to get ready, and then you
can visit the website on `localhost:8080` (or check the Ports tab if you're
using the in-browser experience).

## Issue policy

When opening an issue, please consider the following:

1. Be sure to describe the issue in detail. Include demo code and/or
   screenshots if possible.
2. Mention your platform and relevant versions. As a minumum, mention your OS
   and PHP version (`php -v` is your friend here).
3. Don't be a dick.

## Pull request policy

When creating a policy file, please keep the following in mind:

1. Describe your changes, and if any tests are affected. If there's a relevant
   issue, mention that too.
2. When changing dependencies, also add their lockfiles (`composer.lock` for
   Composer, `package-lock.json` for Node)
3. Don't commit IDE-specific files (like the `.idea` folder), they often
   contain absolute paths, which won't work across systems.

If at all possible, please sign the last commit of your PR.
