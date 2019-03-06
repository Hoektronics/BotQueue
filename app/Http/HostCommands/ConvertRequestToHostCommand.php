<?php

namespace App\Http\HostCommands;


use App\Enums\HostRequestStatusEnum;
use App\Errors\ErrorResponse;
use App\Errors\HostErrors;
use App\Exceptions\CannotConvertHostRequestToHost;
use App\Exceptions\HostRequestAlreadyDeleted;
use App\Exceptions\OauthHostClientNotSetup;
use App\Exceptions\OauthHostKeysMissing;
use App\HostRequest;
use App\Http\Resources\HostResource;
use Illuminate\Support\Collection;

class ConvertRequestToHostCommand
{
    use HostCommandTrait;

    protected $ignoreHostAuth = true;

    /**
     * @param $data Collection
     * @return ErrorResponse|HostResource
     * @throws CannotConvertHostRequestToHost
     */
    public function __invoke($data)
    {
        $host_request = HostRequest::find($data->get("id"));

        if($host_request == null) {
            return HostErrors::hostRequestNotFound();
        }

        if($host_request->status !== HostRequestStatusEnum::CLAIMED) {
            return HostErrors::hostRequestIsNotClaimed();
        }

        try {
            $host = $host_request->toHost();
        } catch (OauthHostClientNotSetup $e) {
            return HostErrors::oauthHostClientIsNotSetup();
        } catch (OauthHostKeysMissing $e) {
            return HostErrors::oauthHostKeysMissing();
        } catch (HostRequestAlreadyDeleted $e) {
            return HostErrors::hostRequestNotFound();
        }

        return new HostResource($host);
    }
}