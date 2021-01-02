@setup
    // Check required variables
    $required = [
        'remote' => 'clone URL',
        'branch' => 'branch'
    ];

    // Check required vars
    foreach ($required as $var => $label) {
        if (empty($$var)) {
            throw new Exception("The $label has not been set. Set it using --$var=[value]");
        }
    }

    // Get branch name
    $branchBits = explode('/', $branch);
    $branch = array_pop($branchBits);

    // Get hash if missing
    $hash ??= trim(`git log -1 --format='%H'`);

    // Set env from branch
    $env = $branch === 'master' ? 'production' : 'staging';

    // Settings
    $logFormat = '%h %s (%cr, %cn)'; // see `man git log`

    // Deploy name
    $deployName = (new DateTime())->format('Y-m-d--H-i-s');

    // Paths
    $root = "\$HOME/laravel";
    $deployPath = "$root/deployments/actions/$env/$deployName";
    $livePath = "$root/live/$env";
    $envPath = "$root/environments/$env.env";
    $storagePath = "$root/storage/$env";
    $backupOldPath = "$root/deployments/actions/$env/backup-{$deployName}";
    $branchSlug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($branch)), '-');

    // Paths that must exist
    $paths = [
        $root,
        dirname($livePath),
        dirname($envPath),
        $storagePath,
        dirname($backupOldPath),
    ];
@endsetup

@servers(['web' => 'deploy.local'])

@story('deploy')
    deployment_init
    deployment_clone
    deployment_describe
    deployment_link
    deployment_swap_nova
    deployment_install
    deployment_build
    deployment_down
    deployment_migrate
    deployment_cache
    deployment_up
    restart_horizon
    deployment_cleanup
@endstory

@task('deployment_init')
    {{-- Pre-deploy validation --}}
    echo -e "\nEnsuring working directories exist"
    @foreach ($paths as $path)
    test -d "{{ $path }}" || mkdir -p "{{ $path }}"
    @endforeach

    {{-- Make deployment directory --}}
    echo -e "\nCreating clone path"
    mkdir -p "{{ $deployPath }}"

    if [ -L "{{ $livePath }}" ]; then
        {{-- Report status --}}
        echo -e "\nLive path is currently linked to $( basename "$( realpath "{{ $livePath }}/" )" )"
    elif [ -d "{{ $livePath }}" ]; then
        {{-- Move live directory if it's a normal directory--}}
        echo -e "\nMoving live path to $( basename "{{ $backupOldPath }}" )"
        mv "{{ $livePath }}" "{{ $backupOldPath }}"
        ln -s "{{ $backupOldPath }}" "{{ $livePath }}"
    elif [ ! -L "{{ $livePath }}" ]; then
        {{-- Ensure a directory exists --}}
        echo -e "\nMaking new current and link it to this deploy"
        ln -s "{{ $deployPath }}" "{{ $livePath }}"
    fi
@endtask

@task('deployment_clone')
    {{-- Enter deploy repo --}}
    cd "{{ $deployPath }}"

    {{-- Clone repo, but don't checkout yet --}}
    echo -e "\nCloning {{ $remote }} and checking out {{ $branch }}."
    git clone \
        --no-checkout \
        --recursive \
        "{{ $remote }}" \
        "{{ $deployPath }}"

    {{-- Repack, removing reference to old repo --}}
    echo -e "\nRe-packing repo"
    git repack -a

    echo -e "\nChecking out {{ $hash }} as 'deployment/{{ $branchSlug }}-{{ $deployName }}'"
    git checkout -b "deployment/{{ $branchSlug }}-{{ $deployName }}" "{{ $hash }}"
@endtask

@task('deployment_describe')
    cd "{{ $deployPath }}"
    {{-- Get latest hash of current and active --}}
    NEW_HASH=$( cd "{{ $deployPath }}" && git log -1 --format='%H' )
    OLD_HASH=$( cd "{{ $livePath }}" && git log -1 --format='%H' )

    {{-- Also get log of old version --}}
    NEW_VERSION=$( cd "{{ $deployPath }}" && git log -1 --format="{{ $logFormat }}" )
    OLD_VERSION=$( cd "{{ $livePath }}" && git log -1 --format="{{ $logFormat }}" )

    {{-- Show diff --}}
    echo -e "\n"
    echo "Currently live: ${OLD_VERSION}"
    echo "Currently deploying: ${NEW_VERSION}"
    echo -e "\nChanges since last version:\n"
    git log --decorate --graph --format="{{ $logFormat }}" "${OLD_HASH}..${NEW_HASH}" 2>dev/null || true
@endtask

@task('deployment_link')
    {{-- Ensure data is available --}}
    if [ ! -d "{{ $storagePath }}" ]; then
        cp -r "{{ $deployPath }}/storage" "{{ $storagePath }}"
    fi

    {{-- Make directories and files --}}
    echo -e "\nRemove existing storage/"
    rm -r "{{ $deployPath }}/storage"

    echo -e "\nLink storage"
    ln -s "{{ $storagePath }}" "{{ $deployPath }}/storage"
    ln -s "{{ $envPath }}" "{{ $deployPath }}/.env"
@endtask

@task('deployment_swap_nova')
    {{-- Report status --}}
    echo -e "\nReplacing Nova dummy with actual installation"

    {{-- Ensure file exists --}}
    if [ -f "$HOME/nova.zip" ]; then
        {{-- Remove existing install --}}
        echo -e "\nRemoving dummy package..."
        rm -rf "{{ $deployPath }}/library/composer/nova"

        {{-- Unzip archive --}}
        echo -e "\nExtracing archive..."
        cd "{{ $deployPath }}/library/composer"
        unzip "$HOME/nova.zip"

        {{-- Move to right location --}}
        echo -e "\nMoving extracted contents..."
        mv laravel-nova-* nova

        {{-- Make the required alias file --}}
        echo -e "\nMaking an alias file..."
        touch nova/aliases.php

        echo -e "\nLaravel Nova extracted in library"
    else
        {{-- Report missing --}}
        echo "Nova archive file not found."
        echo "*** NOVA WILL NOT BE INSTALLED ***"
    fi
@endtask

@task('deployment_install')
    {{-- Go to root dir --}}
    cd "{{ $deployPath }}"

    {{-- Install NodeJS files --}}
    echo -e "\nInstalling NodeJS dependencies"
    npm install --also=dev

    {{-- Install Composer deps --}}
    echo -e "\nInstalling Composer dependencies"
    composer \
        --apcu-autoloader \
        --classmap-authoritative \
        --no-dev \
        --no-interaction \
        --no-progress \
        --no-suggest \
        install

    {{-- Link public storage --}}
    echo -e "\nLink public directory to storage"
    php "{{ $deployPath }}/artisan" storage:link
@endtask

@task('deployment_build')
    cd "{{ $deployPath }}"

    echo -e "\nBuilding front-end"
    npm run build
@endtask

@task('deployment_down')
    cd "{{ $deployPath }}"

    echo -e "\nStopping Laravel Horizon"
    php artisan horizon:terminate --wait

    {{-- Pull down new and current app --}}
    echo -e "\nPulling down platform"
    php artisan down --retry=5
    php "{{ $livePath }}/artisan" down --retry=5

    echo -e "\nClearing optimizations"
    php "{{ $livePath }}/artisan" optimize:clear
@endtask

@task('deployment_migrate')
    cd "{{ $deployPath }}"

    {{-- Migrate database --}}
    echo -e "\nMigrating database"
    php artisan migrate --force --seed
@endtask

@task('deployment_cache')
    cd "{{ $deployPath }}"

    {{-- Optimize application --}}
    echo -e "\nOptimizing application"
    php artisan optimize
    php artisan event:cache
@endtask

@task('deployment_up')
    cd "{{ $deployPath }}"

    {{-- Make backlink to current version --}}
    OLD_PATH="$( realpath "{{ $livePath }}/" )"
    ln -s "${OLD_PATH}" "{{ $deployPath }}/_previous"

    {{-- Switch active version --}}
    echo "Switching from $( basename "${OLD_PATH}" ) to $( basename "{{ $deployPath }}" )"
    rm "{{ $livePath }}"
    ln -s "{{ $deployPath }}" "{{ $livePath }}"

    {{-- Start up the server again --}}
    echo -e "\nGoing live"
    php artisan up

    {{-- Update attachments --}}
    {{-- echo -e "\nUpdating attachments..."
    php artisan paperclip:refresh App\\Models\\Activity || true
    php artisan paperclip:refresh App\\Models\\Sponsor || true
    php artisan paperclip:refresh App\\Models\\NewsItem || true --}}

    {{-- Get URL --}}
    source .env
    echo -e "\nApplication is live at ${APP_URL}."
    echo ">>URL = ${APP_URL}"
@endtask

@task('deployment_cleanup')
    find "$( dirname "{{ $deployPath }}" )" -maxdepth 1 -name "20*" | sort | head -n -4 | xargs rm -Rf
    echo "Cleaned up old deployments"
@endtask

@story('rollback')
    deployment_rollback
    restart_horizon
@endstory

@task('deployment_rollback')
    cd "{{ $root }}"
    if [ ! -L "{{ $livePath }}/_previous" ]; then
        echo "Rollback not supported for this release"
        exit 1
    fi

    if [ ! -e "{{ $livePath }}/_previous/artisan" ]; then
        echo "Previous release has been pruned"
        exit 1
    fi

    OLD_VERSION="$( realpath "{{ $livePath }}/_previous" )"
    if [ "$( realpath "${OLD_PATH}" )" = "$( realpath "${{ $livePath }}" )" ]; then
        echo "Already at latest version"
        exit 1
    fi

    echo -e "\nGoing dark"
    php artisan down --retry=5

    echo -e "\nRolling back to $( basename "${OLD_VERSION}" )"
    rm "{{ $livePath }}"
    ln -s "${OLD_VERSION}" "{{ $livePath }}"

    echo "Re-running caching"
    php artisan optimize:clear
    php artisan optimize
    php artisan event:cache

    echo -e "\nGoing back online"
    php artisan up

    echo -e "\nRolled back to $( basename "${OLD_VERSION}" )"
@endtask

@task('restart_horizon')
    cd "{{ $livePath }}"

    {{-- Un-pause horizon --}}
    echo -e "\nRestarting Laravel Horizon"
    php artisan horizon:continue
    php artisan horizon:purge
@endtask
