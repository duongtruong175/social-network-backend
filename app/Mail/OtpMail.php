<?php

namespace App\Mail;

use App\Models\Otp;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Otp $otp)
    {
        //
        $this->otp = $otp;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = "";
        $content = "";
        // mail register
        if ($this->otp->type == 1) {
            $subject = "Xác minh email";
            $content = "Cảm ơn bạn đã bắt đầu quá trình tạo tài khoản mới. Chúng tôi muốn đảm bảo rằng đó thực sự là bạn. Vui lòng nhập mã xác minh sau đây khi được nhắc.";
        } else if ($this->otp->type == 2) {
            $subject = "Mã xác nhận đặt lại mật khẩu";
            $content = "Bạn có yêu cầu đặt lại mật khẩu. Dưới đây là mã xác minh để chắc chắn đó là yêu cầu của bạn. Vui lòng nhập mã xác minh sau đây khi được nhắc.";
        }

        return $this->markdown('mail.otp', [
            'code' => $this->otp->code,
            'content' => $content
        ])->subject($subject);
    }
}
