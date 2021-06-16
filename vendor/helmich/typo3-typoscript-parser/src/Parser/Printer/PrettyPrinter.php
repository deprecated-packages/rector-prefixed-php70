<?php

declare (strict_types=1);
namespace RectorPrefix20210616\Helmich\TypoScriptParser\Parser\Printer;

use RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\Comment;
use RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\ConditionalStatement;
use RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\DirectoryIncludeStatement;
use RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\FileIncludeStatement;
use RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\IncludeStatement;
use RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\MultilineComment;
use RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\NestedAssignment;
use RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\NopStatement;
use RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\Operator\BinaryObjectOperator;
use RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\Operator\Copy;
use RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\Operator\Delete;
use RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\Operator\Modification;
use RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\Operator\Reference;
use RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\Statement;
use RectorPrefix20210616\Symfony\Component\Console\Output\OutputInterface;
/**
 * Printer class that generates TypoScript code from an AST
 *
 * @package    Helmich\TypoScriptParser
 * @subpackage Parser\Printer
 */
class PrettyPrinter implements \RectorPrefix20210616\Helmich\TypoScriptParser\Parser\Printer\ASTPrinterInterface
{
    /**
     * @var PrettyPrinterConfiguration
     */
    private $prettyPrinterConfiguration;
    public function __construct(\RectorPrefix20210616\Helmich\TypoScriptParser\Parser\Printer\PrettyPrinterConfiguration $prettyPrinterConfiguration = null)
    {
        $this->prettyPrinterConfiguration = $prettyPrinterConfiguration ?? \RectorPrefix20210616\Helmich\TypoScriptParser\Parser\Printer\PrettyPrinterConfiguration::create();
    }
    /**
     * @return void
     */
    public function setPrettyPrinterConfiguration(\RectorPrefix20210616\Helmich\TypoScriptParser\Parser\Printer\PrettyPrinterConfiguration $prettyPrinterConfiguration)
    {
        $this->prettyPrinterConfiguration = $prettyPrinterConfiguration;
    }
    /**
     * @param Statement[]     $statements
     * @param OutputInterface $output
     * @return void
     */
    public function printStatements(array $statements, \RectorPrefix20210616\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $this->printStatementList($statements, $output, 0);
    }
    /**
     * @param Statement[]     $statements
     * @param OutputInterface $output
     * @param int             $nesting
     * @return void
     */
    private function printStatementList(array $statements, \RectorPrefix20210616\Symfony\Component\Console\Output\OutputInterface $output, int $nesting = 0)
    {
        $indent = $this->getIndent($nesting);
        $count = \count($statements);
        for ($i = 0; $i < $count; $i++) {
            $statement = $statements[$i];
            if ($statement instanceof \RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\NestedAssignment) {
                $this->printNestedAssignment($output, $nesting, $statement);
            } elseif ($statement instanceof \RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment) {
                $this->printAssignment($output, $statement, $indent);
            } elseif ($statement instanceof \RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\Operator\BinaryObjectOperator) {
                $this->printBinaryObjectOperator($output, $statement, $nesting);
            } elseif ($statement instanceof \RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\Operator\Delete) {
                $output->writeln($indent . $statement->object->relativeName . ' >');
            } elseif ($statement instanceof \RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\Operator\Modification) {
                $output->writeln(\sprintf("%s%s := %s(%s)", $indent, $statement->object->relativeName, $statement->call->method, $statement->call->arguments));
            } elseif ($statement instanceof \RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\ConditionalStatement) {
                $next = $i + 1 < $count ? $statements[$i + 1] : null;
                $previous = $i - 1 >= 0 ? $statements[$i - 1] : null;
                $this->printConditionalStatement($output, $nesting, $statement, $next instanceof \RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\ConditionalStatement, $previous instanceof \RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\ConditionalStatement);
            } elseif ($statement instanceof \RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\IncludeStatement) {
                $this->printIncludeStatement($output, $statement);
            } elseif ($statement instanceof \RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\Comment) {
                $output->writeln($indent . $statement->comment);
            } elseif ($statement instanceof \RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\MultilineComment) {
                $output->writeln($indent . $statement->comment);
            } elseif ($statement instanceof \RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\NopStatement) {
                $this->printNopStatement($output);
            }
        }
    }
    private function getIndent(int $nesting) : string
    {
        return \str_repeat($this->prettyPrinterConfiguration->getIndentation(), $nesting);
    }
    /**
     * @return void
     */
    private function printBinaryObjectOperator(\RectorPrefix20210616\Symfony\Component\Console\Output\OutputInterface $output, \RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\Operator\BinaryObjectOperator $operator, int $nesting)
    {
        $targetObjectPath = $operator->target->relativeName;
        if ($operator instanceof \RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\Operator\Copy) {
            $output->writeln($this->getIndent($nesting) . $operator->object->relativeName . ' < ' . $targetObjectPath);
        } elseif ($operator instanceof \RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\Operator\Reference) {
            $output->writeln($this->getIndent($nesting) . $operator->object->relativeName . ' =< ' . $targetObjectPath);
        }
    }
    /**
     * @return void
     */
    private function printIncludeStatement(\RectorPrefix20210616\Symfony\Component\Console\Output\OutputInterface $output, \RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\IncludeStatement $statement)
    {
        if ($statement instanceof \RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\FileIncludeStatement) {
            $this->printFileIncludeStatement($output, $statement);
        } elseif ($statement instanceof \RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\DirectoryIncludeStatement) {
            $this->printDirectoryIncludeStatement($output, $statement);
        }
    }
    /**
     * @return void
     */
    private function printFileIncludeStatement(\RectorPrefix20210616\Symfony\Component\Console\Output\OutputInterface $output, \RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\FileIncludeStatement $statement)
    {
        if ($statement->newSyntax) {
            $output->writeln('@import \'' . $statement->filename . '\'');
        } else {
            $attributes = "";
            if ($statement->condition) {
                $attributes = ' condition="' . $statement->condition . '"';
            }
            $output->writeln('<INCLUDE_TYPOSCRIPT: source="FILE:' . $statement->filename . '"' . $attributes . '>');
        }
    }
    /**
     * @return void
     */
    private function printDirectoryIncludeStatement(\RectorPrefix20210616\Symfony\Component\Console\Output\OutputInterface $output, \RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\DirectoryIncludeStatement $statement)
    {
        $attributes = "";
        if ($statement->extensions) {
            $attributes .= ' extensions="' . $statement->extensions . '"';
        }
        if ($statement->condition) {
            $attributes .= ' condition="' . $statement->condition . '"';
        }
        $includeStmt = '<INCLUDE_TYPOSCRIPT: source="DIR:' . $statement->directory . '"' . $attributes . '>';
        $output->writeln($includeStmt);
    }
    /**
     * @param OutputInterface  $output
     * @param int              $nesting
     * @param NestedAssignment $statement
     * @return void
     */
    private function printNestedAssignment(\RectorPrefix20210616\Symfony\Component\Console\Output\OutputInterface $output, $nesting, \RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\NestedAssignment $statement)
    {
        $output->writeln($this->getIndent($nesting) . $statement->object->relativeName . ' {');
        $this->printStatementList($statement->statements, $output, $nesting + 1);
        $output->writeln($this->getIndent($nesting) . '}');
    }
    /**
     * @param OutputInterface      $output
     * @param int                  $nesting
     * @param ConditionalStatement $statement
     * @param bool                 $hasNext
     * @param bool                 $hasPrevious
     * @return void
     */
    private function printConditionalStatement(\RectorPrefix20210616\Symfony\Component\Console\Output\OutputInterface $output, int $nesting, \RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\ConditionalStatement $statement, bool $hasNext = \false, bool $hasPrevious = \false)
    {
        if (!$hasPrevious) {
            $output->writeln('');
        }
        $output->writeln($statement->condition);
        $this->printStatementList($statement->ifStatements, $output, $nesting);
        if (\count($statement->elseStatements) > 0) {
            $output->writeln('[else]');
            $this->printStatementList($statement->elseStatements, $output, $nesting);
        }
        if ($this->closeCondition($hasNext)) {
            $output->writeln('[global]');
        }
    }
    /**
     * @param OutputInterface $output
     * @param Assignment      $statement
     * @param string          $indent
     * @return void
     */
    private function printAssignment(\RectorPrefix20210616\Symfony\Component\Console\Output\OutputInterface $output, \RectorPrefix20210616\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment $statement, string $indent)
    {
        if (\strpos($statement->value->value, "\n") !== \false) {
            $output->writeln($indent . $statement->object->relativeName . ' (');
            $output->writeln(\rtrim($statement->value->value));
            $output->writeln($indent . ')');
            return;
        }
        $output->writeln($indent . $statement->object->relativeName . ' = ' . $statement->value->value);
    }
    /**
     * @return void
     */
    private function printNopStatement(\RectorPrefix20210616\Symfony\Component\Console\Output\OutputInterface $output)
    {
        if ($this->prettyPrinterConfiguration->shouldIncludeEmptyLineBreaks()) {
            $output->writeln('');
        }
    }
    private function closeCondition(bool $hasNext) : bool
    {
        return !$hasNext || $this->prettyPrinterConfiguration->shouldAddClosingGlobal();
    }
}
