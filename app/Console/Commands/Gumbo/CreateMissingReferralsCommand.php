<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo;

use App\Models\JoinSubmission;
use App\Models\MemberReferral;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;

class CreateMissingReferralsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
    gumbo:create-missing-referrals
        {--since=-6 months : The start date to create items for}
        {--dry-run : Only pretend}
    CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $query = JoinSubmission::query()
            ->whereNotNull('referrer');

        $since = $this->option('since');
        if ($since !== 'all') {
            $query->where('created_at', '>', Date::parse($since));
        }

        $query->each(fn (JoinSubmission $submission) => $this->createMissing($submission));
    }

    public function createMissing(JoinSubmission $submission): void
    {
        if (
            MemberReferral::query()
                ->whereSubject($submission->first_name)
                ->whereReferredBy($submission->referrer)
                ->exists()
        ) {
            return;
        }

        if ($this->option('dry-run')) {
            $this->line(sprintf(
                'Would create referral for <info>%s</>, referred by <comment>%s</>',
                $submission->first_name,
                $submission->referrer,
            ));

            return;
        }

        $referral = new MemberReferral([
            'subject' => $submission->first_name,
            'referred_by' => $submission->referrer,
        ]);

        $referral->created_at = $submission->created_at;
        $referral->save();

        $this->line(sprintf(
            'Created referral for <info>%s</>, referred by <comment>%s</>',
            $submission->first_name,
            $submission->referrer,
        ));
    }
}
