<?php

namespace App\Console\Commands;

use App\Listeners\TransferIssueNotification;
use App\Models\TransferIssue;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Telegram\Bot\Laravel\Facades\Telegram;

class EscalateTransferIssues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'issues:escalate {--postponed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $bot = Telegram::bot('status');

        $issues = TransferIssue::where(function (Builder $query) {
            $query->where('called', false)
                ->orWhere('processed', false);
        });

        if ($this->option('postponed')) {
            $issues->where('postponed', true);
        } else {
            $now = now('Europe/Moscow');
            $hour = $now->hour;

            // scheduled to run only during working hours
            if ($hour < 11) { // pick up yesterday evening issues
                $gracePeriodHours = 14;
            } else {
                $gracePeriodHours = 2;
            }
            $issues->where('created_at', '<', $now->subHours($gracePeriodHours));
        }

        $issues = $issues->get();

        foreach ($issues as $issue) {
            $type = match ($issue->type) {
                'reschedule' => "Перенос",
                'refusal' => "Отказ",
                default => "??? Непонятно, отказ или перенос, перешлите программисту",
            };

            $callText = $issue->called ? "Звонок был" : "Звонка не было";
            $processedText = $issue->processed ? "обработан" : "не обработан";


            $text = "<b>$type\n$callText, $processedText</b>\n\n";

            if ($issue->reason) {
                $text .= "<b>Причина</b>\n" . $issue->reason . "\n\n";
            }

            if ($issue->result) {
                $text .= "<b>Результат</b>\n" . $issue->result . "\n\n";
            }

            $text .= $issue->description;

            $bot->sendMessage([
                'chat_id' => config('telegram.chats.transfer_supervisors'),
                'text' => $text,
                'parse_mode' => 'html']);

            $notifier = new TransferIssueNotification();
            $notifier->updateMessage($issue, escalation: true);

            $issue->delete();
        }
    }
}
