<?php

namespace Remp\MailerModule\Api\v1\Handlers\Users;

use Remp\MailerModule\Api\JsonValidationTrait;
use Remp\MailerModule\Api\v1\Handlers\Mailers\Traits\ParseLogFilterConditionsTrait;
use Remp\MailerModule\Repository\LogsRepository;
use Tomaj\NetteApi\Handlers\BaseHandler;
use Tomaj\NetteApi\Params\InputParam;
use Tomaj\NetteApi\Response\JsonApiResponse;

class LogsCountHandler extends BaseHandler
{
    private $logsRepository;

    use JsonValidationTrait;
    use ParseLogFilterConditionsTrait;

    public function __construct(
        LogsRepository $logsRepository
    ) {
        parent::__construct();
        $this->logsRepository = $logsRepository;
    }

    public function params()
    {
        return [
            new InputParam(InputParam::TYPE_POST_RAW, 'raw')
        ];
    }

    public function handle($params)
    {
        $payload = $this->validateInput($params['raw'], __DIR__ . '/logs-count.schema.json');

        if ($this->hasErrorResponse()) {
            return $this->getErrorResponse();
        }

        $logs = $this->logsRepository->allForEmail($payload['email']);

        foreach ($this->parseConditions($payload['filter'] ?? []) as $where) {
            $logs->where(...$where);
        }

        if (isset($payload['mail_template_ids'])) {
            $logs->where('mail_template_id', $payload['mail_template_ids']);
        }

        return new JsonApiResponse(200, $logs->count('*'));
    }
}