<?php

namespace App\Extensions;

use App\Models\User\User;
use Illuminate\Session\DatabaseSessionHandler;


/**
 * When we are reading session from the internal database,
 * the user_id refer to the user id on the internal system.
 *
 * When loading session, we should then replace the internal user id into this systems' user id
 * And vice versa when saving session.
 *
 */
class CustomDatabaseSessionHandler extends DatabaseSessionHandler
{
    /**
     * {@inheritdoc}
     *
     * @return string|false
     */
    public function read($sessionId): string|false
    {
        $session = (object)$this->getQuery()->find($sessionId);

        if ($this->expired($session)) {
            $this->exists = true;

            return '';
        }

        if (isset($session->payload)) {
            $this->exists = true;

            $payload = unserialize(base64_decode($session->payload));

            foreach ($payload as $key => $value) {
                if (str_starts_with($key, 'login_web')) {

                    $payload[$key] = User::where('internal_user_id', $value)->first()->id;
                    return serialize($payload);
                }
            }

            return base64_decode($session->payload);
        }

        return '';
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function write($sessionId, $data): bool
    {
        $payloadWrapper = $this->getDefaultPayload($data);

        $payload = unserialize(base64_decode($payloadWrapper['payload']));

        foreach ($payload as $key => $value) {
            if (str_starts_with($key, 'login_web')) {

                $user                      = User::where('id', $value)->first();
                $payload[$key]             = $user->internal_user_id;

                $payloadWrapper['payload'] = base64_encode(serialize($payload));
                $payloadWrapper['user_id'] = $user->internal_user_id;

                break;
            }
        }

        if (!$this->exists) {
            $this->read($sessionId);
        }

        if ($this->exists) {
            $this->performUpdate($sessionId, $payloadWrapper);
        } else {
            $this->performInsert($sessionId, $payloadWrapper);
        }

        return $this->exists = true;
    }
}
