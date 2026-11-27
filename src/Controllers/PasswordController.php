<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\App;
use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;
use App\Services\LoginThrottle;
use App\Services\PasswordResetService;

/**
 * Forgot-password and reset flow.
 */
final class PasswordController extends BaseController
{
    private PasswordResetService $resets;
    private LoginThrottle $throttle;

    public function __construct(App $app, AuthService $auth, PasswordResetService $resets, LoginThrottle $throttle)
    {
        parent::__construct($app, $auth);
        $this->resets = $resets;
        $this->throttle = $throttle;
    }

    public function showForgot(Request $request): Response
    {
        $html = $this->app->view->render('auth/forgot', [
            'pageTitle' => $this->app->i18n->t('auth.forgot_title'),
            'user' => null,
        ]);

        return Response::html($html);
    }

    public function forgot(Request $request): Response
    {
        if (($csrfError = $this->verifyCsrf($request, '/forgot')) !== null) {
            return $csrfError;
        }

        $email = trim((string) $request->post('email', ''));
        // Per IP beperken, zodat niemand onze mailserver kan misbruiken om mails te spammen
        $throttleKey = $request->ip();
        if ($this->throttle->tooManyAttempts('forgot', $throttleKey)) {
            $this->app->session->flash('error', $this->app->i18n->t('auth.too_many_attempts'));
            return Response::redirect($request->url('/forgot'));
        }
        $this->throttle->hit('forgot', $throttleKey);

        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $debugLink = $this->resets->request(
                $email,
                static fn (string $token): string => $request->absoluteUrl('/reset/' . $token)
            );
            if ($debugLink !== null) {
                $this->app->session->flash('info', $this->app->i18n->t('auth.forgot_debug', ['link' => $debugLink]));
            }
        }

        // Always the same neutral message, whether the account exists or not.
        $this->app->session->flash('success', $this->app->i18n->t('auth.forgot_sent'));
        return Response::redirect($request->url('/forgot'));
    }

    /**
     * @param array<string, string> $params
     */
    public function showReset(Request $request, array $params): Response
    {
        $token = (string) ($params['token'] ?? '');
        if (!$this->resets->isValid($token)) {
            $this->app->session->flash('error', $this->app->i18n->t('auth.reset_invalid'));
            return Response::redirect($request->url('/forgot'));
        }

        $html = $this->app->view->render('auth/reset', [
            'pageTitle' => $this->app->i18n->t('auth.reset_title'),
            'user' => null,
            'token' => $token,
        ]);

        return Response::html($html);
    }

    /**
     * @param array<string, string> $params
     */
    public function reset(Request $request, array $params): Response
    {
        $token = (string) ($params['token'] ?? '');
        $path = '/reset/' . $token;

        if (($csrfError = $this->verifyCsrf($request, $path)) !== null) {
            return $csrfError;
        }

        $password = (string) $request->post('password', '');
        $confirm = (string) $request->post('password_confirm', '');

        if (strlen($password) < 6) {
            $this->app->session->flash('error', $this->app->i18n->t('error.password_short'));
            return Response::redirect($request->url($path));
        }

        if ($password !== $confirm) {
            $this->app->session->flash('error', $this->app->i18n->t('auth.password_mismatch'));
            return Response::redirect($request->url($path));
        }

        if (!$this->resets->reset($token, $password)) {
            $this->app->session->flash('error', $this->app->i18n->t('auth.reset_invalid'));
            return Response::redirect($request->url('/forgot'));
        }

        $this->app->session->flash('success', $this->app->i18n->t('auth.reset_done'));
        return Response::redirect($request->url('/login'));
    }
}
