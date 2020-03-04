# Website Use Cases

This file lays out the use cases for the Gumbo Millennium website, which is due
to be released at some point before 11 september 2049.  The file is divided by
user level, which scales up from guest to board member (the highest role). Note
that there's also an undocumented "DC" role which has all permissions.

## Guest level

The basic level, all users of the site are allowed to perform these actions.

1. View the homepage
2. View activities on the website
3. View news on the website
4. View pages on the website
5. Login on the website
6. Register on the website
7.  Reset my password on the website

## User level

The basic level for any user with an account.

1. Logout from my account
2. Enroll for an activity
3. Unenroll from an activity
4. Post a comment on an activity
5. Post a comment on a news article

## Member level

The lowest level for all members of Gumbo Millennium.

1. View files in the file system
2. Download files in the file system
3. Comment on files in the file system

## Organiser level

An organiser of an event. Assigned to the Activiteiten Commissie, but can also
be granted on an individual level.

1. Create a new activity
2. Update existing activities*
3. View enrolled users on activites*
4. Enroll users on activities*
5. Unenroll users on activities*
6. Cancel activities*

*) Users that have an activity assigned to their account (`owner_id`), will
also be able to perform this action, independent of whether or not they're
granted the correct permission.

## Writer level

This level is for the content editors.

1. Create pages
2. Update pages
3. Delete pages
4. Create news articles
5. Update news articles
6. Delete news articles

## Board level

The highest level, allows admin access to the file system and
allows changing of roles.

1. Upload files to filesystem
2. Change files in filesystem
3. Delete files from filesystem
4. Mark files as superseded
5. Mark files as pulled
6. Change user roles
7. Change user permissions
