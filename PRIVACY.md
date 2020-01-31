# Privacy of data

The project stores data, as do most projects. However, since the project is based in the EU,
we need to keep track of the data and make sure personal data is removed after a certain time.

This results in certain checks available under the `gdpr` command namespace. They ensure certain
fields or certain objects are removed after some time.

## About schedules

The system schedules jobs automatically using the Laravel's console kernel. This, however,
does mean that the user should [configure the Laravel schedule runner][1]. Failing to
do this might mean data is stored longer than reported in this document.

[1]: https://laravel.com/docs/5.7/scheduling#introduction

## What data the app stores

The next paragraphs will explain what data is stored, per object type.

### Users

For users (`App\Models\User`) we store a lot of data, but all can be removed at the request of the board.

The following data is stored. The data is, by default, stored indefinitely, until the user
decides to delete their account.

- Username
- First name
- Insertion
- Last name
- E-mail address

**Note**: It's advised to only remove acounts after 90 days since the last action. For this reason,
automatic deletion of users is only done 90 days after they're 'trashed'. If you really, *really* need to
delete a user before this 90 day time limit, issue `gdpr:user-delete --now`.
**This might have unintended side-effects!**

### Downloads

When a user downloads a file, an entry is stored in the database. Each
download results in an entry containing the following data:

- ID of the user
- ID of the file
- Time of download, in UTC
- IP address of the download

The IP address is stored in case we notice weird traffic, since our
documents might contain personal data. The IP address is automatically
removed from the record after approximately 90 days.

You can wipe the records using the `gdpr:clean-download-ips` command.

### Files

Files are uploaded by the board and can only be removed by said board. Since these
files include notes from meetings, there's a chance that these contain sensitive data
which a user might have removed. However, due to the legal foundation these files
present to the student community as a whole, this is up to the board to decide and
is not automated.

Files contain the following data:

- File name
- Author (User ID)
- Metadata (includes Author fields from PDF files)
- Contents (full indexed contents, as read by a machine)

Furthermore, a thumbnail is made of the first page, which *might* contain
photos. This, again, is up to the board to decide.

#### Removing sensitive data

After PDF files are uploaded and repaired, the following data is
scrubbed from the file:

- `PDF:Author` - Set to the owner's "pdf_name"
- `PDF:Creator` - Set to the owner's "pdf_name"
- `PDF:Producer` - Set to app name + 'files.name

However, due to the nature, of PDF files, the original data *will* remain in the file.
The data is merely hidden and can be re-obtained by removing certain meta keys.

#### See for yourself

The IP address removal is located in `App\FileDownload::unsetIpOnOldEntries()`.

## What data can be removd
