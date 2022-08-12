<?php

declare(strict_types=1);

namespace App\Services\Google\WalletObjects\Resource;

use App\Services\Google\WalletObjects\EventTicketClass as EventTicketClassModel;

class EventTicketClass extends \Google\Service\Resource
{
    /**
     * Retrieves an event ticket class referenced by the given class ID. (eventTicketClass.get).
     *
     * @param string $resourceId The unique identifier for a class. This ID must be unique across
     *                           all classes from an issuer. This value should follow the format
     *                           issuer ID.identifier where the former is issued by Google and
     *                           latter is chosen by you.
     * @return EventTicketClassModel
     */
    public function get($resourceId, $optParams = [])
    {
        $params = ['resourceId' => $resourceId];
        $params = array_merge($params, $optParams);

        return $this->call('get', [$params], EventTicketClassModel::class);
    }

    /**
     * Creates a user. (users.insert).
     *
     * @param array $optParams optional parameters
     * @return User
     */
    public function insert(User $postBody, $optParams = [])
    {
        $params = ['postBody' => $postBody];
        $params = array_merge($params, $optParams);

        return $this->call('insert', [$params], User::class);
    }

    /**
     * Retrieves a paginated list of either deleted users or all users in a domain.
     * (users.listUsers).
     *
     * @param array $optParams optional parameters
     *
     * @opt_param string customFieldMask A comma-separated list of schema names. All
     * fields from these schemas are fetched. This should only be set when
     * `projection=custom`.
     * @opt_param string customer The unique ID for the customer's Google Workspace
     * account. In case of a multi-domain account, to fetch all groups for a
     * customer, fill this field instead of domain. You can also use the
     * `my_customer` alias to represent your account's `customerId`. The
     * `customerId` is also returned as part of the [Users resource](/admin-
     * sdk/directory/v1/reference/users). Either the `customer` or the `domain`
     * parameter must be provided.
     * @opt_param string domain The domain name. Use this field to get groups from
     * only one domain. To return all domains for a customer account, use the
     * `customer` query parameter instead. Either the `customer` or the `domain`
     * parameter must be provided.
     * @opt_param string event Event on which subscription is intended (if
     * subscribing)
     * @opt_param int maxResults Maximum number of results to return.
     * @opt_param string orderBy Property to use for sorting results.
     * @opt_param string pageToken Token to specify next page in the list
     * @opt_param string projection What subset of fields to fetch for this user.
     * @opt_param string query Query string for searching user fields. For more
     * information on constructing user queries, see [Search for Users](/admin-
     * sdk/directory/v1/guides/search-users).
     * @opt_param string showDeleted If set to `true`, retrieves the list of deleted
     * users. (Default: `false`)
     * @opt_param string sortOrder Whether to return results in ascending or
     * descending order, ignoring case.
     * @opt_param string viewType Whether to fetch the administrator-only or domain-
     * wide public view of the user. For more information, see [Retrieve a user as a
     * non-administrator](/admin-sdk/directory/v1/guides/manage-
     * users#retrieve_users_non_admin).
     * @return UsersModel
     */
    public function listUsers($optParams = [])
    {
        $params = [];
        $params = array_merge($params, $optParams);

        return $this->call('list', [$params], UsersModel::class);
    }

    /**
     * Makes a user a super administrator. (users.makeAdmin).
     *
     * @param string $userKey Identifies the user in the API request. The value can
     *                        be the user's primary email address, alias email address, or unique user ID.
     * @param array $optParams optional parameters
     */
    public function makeAdmin($userKey, UserMakeAdmin $postBody, $optParams = [])
    {
        $params = ['userKey' => $userKey, 'postBody' => $postBody];
        $params = array_merge($params, $optParams);

        return $this->call('makeAdmin', [$params]);
    }

    /**
     * Updates a user using patch semantics. The update method should be used
     * instead, since it also supports patch semantics and has better performance.
     * This method is unable to clear fields that contain repeated objects
     * (`addresses`, `phones`, etc). Use the update method instead. (users.patch).
     *
     * @param string $userKey Identifies the user in the API request. The value can
     *                        be the user's primary email address, alias email address, or unique user ID.
     * @param array $optParams optional parameters
     * @return User
     */
    public function patch($userKey, User $postBody, $optParams = [])
    {
        $params = ['userKey' => $userKey, 'postBody' => $postBody];
        $params = array_merge($params, $optParams);

        return $this->call('patch', [$params], User::class);
    }

    /**
     * Signs a user out of all web and device sessions and reset their sign-in
     * cookies. User will have to sign in by authenticating again. (users.signOut).
     *
     * @param string $userKey Identifies the target user in the API request. The
     *                        value can be the user's primary email address, alias email address, or unique
     *                        user ID.
     * @param array $optParams optional parameters
     */
    public function signOut($userKey, $optParams = [])
    {
        $params = ['userKey' => $userKey];
        $params = array_merge($params, $optParams);

        return $this->call('signOut', [$params]);
    }

    /**
     * Undeletes a deleted user. (users.undelete).
     *
     * @param string $userKey The immutable id of the user
     * @param array $optParams optional parameters
     */
    public function undelete($userKey, UserUndelete $postBody, $optParams = [])
    {
        $params = ['userKey' => $userKey, 'postBody' => $postBody];
        $params = array_merge($params, $optParams);

        return $this->call('undelete', [$params]);
    }

    /**
     * Updates a user. This method supports patch semantics, meaning you only need
     * to include the fields you wish to update. Fields that are not present in the
     * request will be preserved, and fields set to `null` will be cleared.
     * (users.update).
     *
     * @param string $userKey Identifies the user in the API request. The value can
     *                        be the user's primary email address, alias email address, or unique user ID.
     * @param array $optParams optional parameters
     * @return User
     */
    public function update($userKey, User $postBody, $optParams = [])
    {
        $params = ['userKey' => $userKey, 'postBody' => $postBody];
        $params = array_merge($params, $optParams);

        return $this->call('update', [$params], User::class);
    }

    /**
     * Watches for changes in users list. (users.watch).
     *
     * @param array $optParams optional parameters
     *
     * @opt_param string customFieldMask Comma-separated list of schema names. All
     * fields from these schemas are fetched. This should only be set when
     * projection=custom.
     * @opt_param string customer Immutable ID of the Google Workspace account. In
     * case of multi-domain, to fetch all users for a customer, fill this field
     * instead of domain.
     * @opt_param string domain Name of the domain. Fill this field to get users
     * from only this domain. To return all users in a multi-domain fill customer
     * field instead."
     * @opt_param string event Events to watch for.
     * @opt_param int maxResults Maximum number of results to return.
     * @opt_param string orderBy Column to use for sorting results
     * @opt_param string pageToken Token to specify next page in the list
     * @opt_param string projection What subset of fields to fetch for this user.
     * @opt_param string query Query string search. Should be of the form "".
     * Complete documentation is at https: //developers.google.com/admin-
     * sdk/directory/v1/guides/search-users
     * @opt_param string showDeleted If set to true, retrieves the list of deleted
     * users. (Default: false)
     * @opt_param string sortOrder Whether to return results in ascending or
     * descending order.
     * @opt_param string viewType Whether to fetch the administrator-only or domain-
     * wide public view of the user. For more information, see [Retrieve a user as a
     * non-administrator](/admin-sdk/directory/v1/guides/manage-
     * users#retrieve_users_non_admin).
     * @return Channel
     */
    public function watch(Channel $postBody, $optParams = [])
    {
        $params = ['postBody' => $postBody];
        $params = array_merge($params, $optParams);

        return $this->call('watch', [$params], Channel::class);
    }
}
