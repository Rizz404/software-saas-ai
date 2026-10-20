<?php

namespace App\Modules\Assistant\Tools;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Runs the tool the model asked for, if the registry knows it.
 */
class ToolExecutor
{
    public function __construct(private readonly ToolRegistry $registry) {}

    /**
     * @param  array<string, mixed>  $arguments
     */
    public function execute(ToolContext $context, string $name, array $arguments): ToolResult
    {
        $tool = $this->registry->find($name);

        if (! $tool) {
            return new ToolResult("Unknown tool: {$name}");
        }

        $parameters = $tool->definition()['function']['parameters'];
        $properties = $parameters['properties'] ?? [];
        $arguments = array_intersect_key($arguments, $properties);
        $rules = [];

        foreach ($properties as $field => $schema) {
            $rules[$field] = [
                in_array($field, $parameters['required'] ?? [], true) ? 'required' : 'sometimes',
                match ($schema['type'] ?? 'string') {
                    'integer' => 'integer',
                    'number' => 'numeric',
                    'boolean' => 'boolean',
                    'array', 'object' => 'array',
                    default => 'string',
                },
            ];

            if (isset($schema['enum'])) {
                $rules[$field][] = Rule::in($schema['enum']);
            }

            if (isset($schema['minimum'])) {
                $rules[$field][] = 'min:'.$schema['minimum'];
            }

            if (isset($schema['maximum'])) {
                $rules[$field][] = 'max:'.$schema['maximum'];
            }

            if (($schema['type'] ?? 'string') === 'string') {
                $rules[$field][] = 'max:'.($field === 'summary' ? 2000 : 1000);
            }
        }

        $validator = Validator::make($arguments, $rules);

        if ($validator->fails()) {
            return new ToolResult('Invalid tool arguments: '.implode(', ', $validator->errors()->keys()).'. Ask for valid values before continuing.');
        }

        return $tool->execute($context, $validator->validated());
    }
}
