# Activity enrollment flow

This document describes the enrollment flow for users.

![Activity enrollment diagram](./activity-enrollment.svg)

## Major changes

The biggest changes compared to the current version, is that users who aren't
logged in, will be able to enroll and auto-create an account. I'm considering
designing this in Vue, since then we'd be able to build a proper onboarding
flow, and also make it work nicely with existing sites (such as
landhuisweekend.nl and gumbointro.nl)

### 1. Log in

The first step is to log in. This step is skipped if someone is logged in (of
course), but otherwise the user is asked for his/her email address. This is
then checked against the database for an existing account, and if none is found
the user is asked for first, middle and last name. The password reset link is
automatically sent and the user is logged in.

### 2. Provide info

The user is then, if required, asked to provide the form data. These questions
are supplied by the activity itself and are required before payment starts.

If no questions are set, this is skipped.

### 3. Payment

Payment will automatically select the correct ticket (discounted, if possible,
full price otherwise). The activity host can add "payment plans", but defaults
to single-payment.

The user then selects his/her bank, and is redirected to iDEAL to complete the
transaction.

### 4. Complete

When all is said and done, the user is enrolled.
