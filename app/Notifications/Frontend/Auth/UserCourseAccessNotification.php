<?php

namespace App\Notifications\Frontend\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Auth\User;

/**
 * Class UserCourseAccessNotification.
 */
class UserCourseAccessNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var
     */
    protected $instructor;
    protected $student;

    /**
     * UserNeedsConfirmation constructor.
     *
     * @param $confirmation_code
     */
    public function __construct(User $instructor, User $student)
    {
        $this->instructor = $instructor;
        $this->student = $student;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject(__('strings.emails.course_access.approved.subject'))
            ->line(__('strings.emails.course_access.approved.message') . " {$this->instructor->first_name} {$this->instructor->last_name}")
            ->action(__('strings.emails.course_access.approved.dashboard_link'), route('frontend.user.dashboard'))
            ->line("Thank you");
    }
}
