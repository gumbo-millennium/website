<?php

namespace App\Jobs\Activities;

class CreateActivityMessagesJob extends ActivityJob implements ShouldQueue
{
    private const CACHE_KEY = 'activity.templates.mail';

    private const TEMPLATE_PATH = 'yaml/templates/activity-mails';

    public function handle(): void
    {
        // Get handle
        $activity = $this->activity;
        $activity->loadMissing('messages');

        // Get messages
        $messages = $activity->messages->keyBy('name');

        // Get templates from resource folder
        $templates = $this->getTemplates();

        // Add all items
        foreach ($templates as $template) {
            if ($messages->has($template->name)) {
                continue;
            }

            $this->addTemplate($activity, $template);
        }
    }

    private function getTemplates() {
        // Check for a stored cache
        if (Cache::has(self::CACHE_KEY)) {
            return Cache::get(self::CACHE_KEY)
        }

        $filesystem = new Filesystem();
        $files = $filesystem->allFiles(self::TEMPLATE_PATH);
        $templates = [];
        foreach ($files as $file) {
            if ($filesystem->mime($file) !== 'text/yaml') {
                continue;
            }

            $templates[] = Yaml::parse($filesystem->get($file));
        }

        Cache::put(self::CACHE_KEY, $templates, Date::now()->addHours(24));

        return $templates;
    }

    private function addTemplate(Activity $activity, Template $template): void
    {
        $message = $activity->messages()->create([
            'name' => $template->name,
            'subject' => $template->subject,
            'body' => $template->body,
        ]);

        if ($template->attachments) {
            foreach ($template->attachments as $attachment) {
                $actualPath = resource_path($attachment->path);
                $attachmentName = $attachment->name ?? basename($actualPath);

                $message
                    ->addMedia($actualPath)
                    ->usingName($attachmentName)
                    ->preservingOriginal()
                    ->toMediaCollection('attachments');
            }
        }

        $message->save();
    }
}
