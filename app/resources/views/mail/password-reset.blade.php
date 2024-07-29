<div>
    To reset your password click on the following link:
    <a href="{{ route('password.reset.edit', ['token' => $token, 'user' => $user]) }}">Reset Your Password</a>

    <p>If you did not request a password reset, please ignore this email.</p>
</div>
