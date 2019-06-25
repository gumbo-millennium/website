# Event flow

The user runs the following flow when signing up for events

1. User arrives on homepage (`/`) → `WordPressController@home`
2. User enters credentials at `/auth/login` → `Auth\\LoginController@showLoginForm`
3. User logs in `/auth/login` → `Auth\\LoginController@login` (`POST`)
4. User goes to `/activity` → `ActivityController@index`
4. User goes to `/activity/[slug]` → `ActivityController@view`
5. User enrolls in activity `/enroll/[slug]/create` → `EnrollController@create` (`POST`)
6. User updates enrollment information `/enroll/[slug]/update` → `EnrollmentController@edit`
6. User submits new information `/enroll/[slug]/update` → `EnrollmentController@edit` (`POST`)
7. User pays event (not yet)
8. User is forwarded to payment terminal (not yet)
9. User returns from payment terminal (not yet)
10. User is sent to enrollment status `/enroll/[slug]` → `EnrollmentController@status`

## Unenrollment

1. User logs in
2. User goes to enrollments (*how?*)
3. User views enrollments `/enroll` → `EnrollmentController@index`
4. User goes to details `/enroll/[slug]` → `EnrollmentController@status`
5. User cancels event `/enroll/[slug]/cancel` → `EnrollmentController@unenroll`
6. User confirms cancellation `/enroll/[slug]/cancel` → `EnrollmentController@destroy` (`POST`)
7. User is returned to overview `/enroll` → `EnrollmentController@index`

The system will handle refunding the event price, *with* transaction fees. The user can no longer
enroll via the website.
