<?php

namespace App\Mail;

use App\Models\ClubJoinRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClubJoinRequestRejected extends Mailable
{
    use Queueable, SerializesModels;

    public $joinRequest;
    public $rejectionReason;

    /**
     * Create a new message instance.
     */
    public function __construct(ClubJoinRequest $joinRequest, $rejectionReason = null)
    {
        $this->joinRequest = $joinRequest;
        $this->rejectionReason = $rejectionReason;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Thông báo: Đơn tham gia CLB đã bị từ chối - ' . $this->joinRequest->club->name)
                    ->view('emails.club-join-request-rejected')
                    ->with([
                        'user' => $this->joinRequest->user,
                        'club' => $this->joinRequest->club,
                        'rejectionReason' => $this->rejectionReason,
                    ]);
    }
}





