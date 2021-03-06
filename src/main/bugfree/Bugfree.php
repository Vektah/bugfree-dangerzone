<?php

namespace bugfree;

use bugfree\config\Config;
use bugfree\visitors\NameValidator;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;

/**
 * Parses a file for errors when constructed.
 *
 * The object left after construction time should be fairly lightweight in terms of memory usage, only containing
 * error/warning summary data.
 */
class Bugfree
{
    const VERSION = '0.4.1';

    /** @var Resolver */
    private $resolver;

    /** @var Config */
    private $config;

    /**
     * @param Resolver $resolver resolver to use when checking use statements.
     * @param Config $config
     */
    public function __construct(Resolver $resolver, Config $config)
    {
        $this->resolver = $resolver;
        $this->config = $config;
    }

    /**
     * Parse a source with the given name.
     *
     * @param string   $name    The filename to include in any error messages
     * @param string   $source  The source code to analyze
     *
     * @return Result
     */
    public function parse($name, $source)
    {
        $result = new Result($name, $this->config);
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, new Lexer());

        $nodes = $parser->parse($source);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameValidator($result, $this->resolver));
        $traverser->traverse($nodes);

        return $result;
    }
}
