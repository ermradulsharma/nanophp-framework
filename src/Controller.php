<?php

namespace Nano\Framework;

use Psr\Container\ContainerInterface;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use Psr\Http\Message\ServerRequestInterface;

use Nano\Framework\Auth\Traits\AuthorizesRequests;

class Controller
{
    use AuthorizesRequests;

    protected ContainerInterface $container;

    /**
     * @Inject
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function validate(ServerRequestInterface $request, array $rules)
    {
        $validator = $this->container->get(Factory::class)->make(
            $request->getParsedBody() ?? [],
            $rules
        );

        if ($validator->fails()) {
            throw new \Exception("Validation Failed: " . implode(", ", $validator->errors()->all()));
        }

        return $validator->validated();
    }
}
