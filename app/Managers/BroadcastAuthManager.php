<?php

namespace App\Managers;

use App\Host;
use App\HostManager;
use App\User;
use Illuminate\Container\Container;
use Illuminate\Contracts\Routing\BindingRegistrar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BroadcastAuthManager
{
    private $channels;
    protected $bindingRegistrar;
    /**
     * @var HostManager
     */
    private $hostManager;

    public function __construct($channels, HostManager $hostManager)
    {
        $this->channels = $channels;
        $this->hostManager = $hostManager;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function auth(Request $request)
    {
        $channel = $request->json('channel_name');

        if ($channel === null) {
            throw new BadRequestHttpException('channel_name must be specified');
        }
        $authModel = $this->hostManager->getHost();
        if ($authModel === null) {
            $authModel = $request->user();
        }

        foreach ($this->channels as $pattern => $channelClass) {
            $simplifiedPattern = preg_replace('/\{(.*?)\}/', '*', $pattern);

            if (! Str::is($simplifiedPattern, $channel)) {
                continue;
            }

            $parameters = $this->extractAuthParameters($pattern, $channel);

            try {
                $handler = $this->getCallback($channelClass, $authModel, $parameters);
            } catch (\ReflectionException $e) {
                throw new AccessDeniedException;
            }

            if ($handler()) {
                return response()->json();
            }
        }

        throw new AccessDeniedHttpException;
    }

    /**
     * Extract the parameters from the given pattern and channel.
     *
     * @param  string $pattern
     * @param  string $channel
     * @return Collection
     */
    protected function extractAuthParameters($pattern, $channel)
    {
        return collect($this->extractChannelKeys($pattern, $channel))
            ->reject(function ($value, $key) {
                return is_numeric($key);
            })
            ->map(function ($value, $key) {
                return $this->resolveBinding($key, $value);
            })
            ->values();
    }

    /**
     * Extract the channel keys from the incoming channel name.
     *
     * @param  string $pattern
     * @param  string $channel
     * @return array
     */
    protected function extractChannelKeys($pattern, $channel)
    {
        preg_match('/^'.preg_replace('/\{(.*?)\}/', '(?<$1>[^\.]+)', $pattern).'/', $channel, $keys);

        return $keys;
    }

    /**
     * Resolve the given parameter binding.
     *
     * @param  string $key
     * @param  string $value
     * @return mixed
     */
    protected function resolveBinding($key, $value)
    {
        $binder = $this->binder();

        if ($binder && $binder->getBindingCallback($key)) {
            return call_user_func($binder->getBindingCallback($key), $value);
        }

        return $value;
    }

    /**
     * Format the channel array into an array of strings.
     *
     * @param  array $channels
     * @return array
     */
    protected function formatChannels(array $channels)
    {
        return array_map(function ($channel) {
            return (string) $channel;
        }, $channels);
    }

    /**
     * Get the model binding registrar instance.
     *
     * @return \Illuminate\Contracts\Routing\BindingRegistrar
     */
    protected function binder()
    {
        if (! $this->bindingRegistrar) {
            $this->bindingRegistrar = Container::getInstance()->bound(BindingRegistrar::class)
                ? Container::getInstance()->make(BindingRegistrar::class) : null;
        }

        return $this->bindingRegistrar;
    }

    /**
     * Normalize the given callback into a callable.
     *
     * @param  mixed $callback
     * @return callable|\Closure
     */
    protected function normalizeChannelHandlerToCallable($callback)
    {
        return is_callable($callback) ? $callback : function (...$args) use ($callback) {
            return Container::getInstance()
                ->make($callback)
                ->join(...$args);
        };
    }

    /**
     * @param $channelClass
     * @param $method
     * @return \ReflectionParameter[]
     * @throws \ReflectionException
     */
    private function getParametersFromMethod($channelClass, $method)
    {
        $reflection = new ReflectionClass($channelClass);
        if (! $reflection->hasMethod($method)) {
            throw new AccessDeniedHttpException;
        }

        return $reflection->getMethod($method)->getParameters();
    }

    /**
     * @param $channelClass
     * @param Model $authModel
     * @param Collection $parameters
     * @return \Closure
     * @throws \ReflectionException
     */
    private function getCallback($channelClass, $authModel, $parameters)
    {
        if ($authModel instanceof User) {
            return $this->getCallbackFromMethod($channelClass, $authModel, $parameters, 'user');
        }

        if ($authModel instanceof Host) {
            return $this->getCallbackFromMethod($channelClass, $authModel, $parameters, 'host');
        }

        throw new AccessDeniedHttpException('Unknown authentication model');
    }

    /**
     * @param $channelClass
     * @param $parameters
     * @param $method
     * @return \Closure
     * @throws \ReflectionException
     */
    private function getCallbackFromMethod($channelClass, $authModel, $parameters, $method): \Closure
    {
        $requiredParameters = collect($this->getParametersFromMethod($channelClass, $method));

        $requiredParameters = $requiredParameters->slice(1);

        $args = $this->matchParameters($requiredParameters, $parameters);

        return function () use ($channelClass, $authModel, $args, $method) {
            return Container::getInstance()
                ->make($channelClass)
                ->$method($authModel, ...$args);
        };
    }

    /**
     * @param Collection $requiredParameters
     * @param Collection $parameters
     * @return array
     */
    private function matchParameters($requiredParameters, $parameters)
    {
        return $requiredParameters->map(function ($reflectionParameter) use ($parameters) {
            /* @var \ReflectionParameter $reflectionParameter */
            return $parameters->first(function ($filledParameter) use ($reflectionParameter) {
                return $reflectionParameter->getClass()->isInstance($filledParameter);
            });
        })->all();
    }
}
