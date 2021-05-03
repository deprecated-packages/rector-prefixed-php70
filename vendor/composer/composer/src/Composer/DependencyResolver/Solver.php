<?php

/*
 * This file is part of Composer.
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20210503\Composer\DependencyResolver;

use RectorPrefix20210503\Composer\IO\IOInterface;
use RectorPrefix20210503\Composer\Package\PackageInterface;
use RectorPrefix20210503\Composer\Repository\PlatformRepository;
/**
 * @author Nils Adermann <naderman@naderman.de>
 */
class Solver
{
    const BRANCH_LITERALS = 0;
    const BRANCH_LEVEL = 1;
    /** @var PolicyInterface */
    protected $policy;
    /** @var Pool */
    protected $pool;
    /** @var RuleSet */
    protected $rules;
    /** @var RuleWatchGraph */
    protected $watchGraph;
    /** @var Decisions */
    protected $decisions;
    /** @var PackageInterface[] */
    protected $fixedMap;
    /** @var int */
    protected $propagateIndex;
    /** @var array[] */
    protected $branches = array();
    /** @var Problem[] */
    protected $problems = array();
    /** @var array */
    protected $learnedPool = array();
    /** @var array */
    protected $learnedWhy = array();
    /** @var bool */
    public $testFlagLearnedPositiveLiteral = \false;
    /** @var IOInterface */
    protected $io;
    /**
     * @param PolicyInterface $policy
     * @param Pool            $pool
     * @param IOInterface     $io
     */
    public function __construct(\RectorPrefix20210503\Composer\DependencyResolver\PolicyInterface $policy, \RectorPrefix20210503\Composer\DependencyResolver\Pool $pool, \RectorPrefix20210503\Composer\IO\IOInterface $io)
    {
        $this->io = $io;
        $this->policy = $policy;
        $this->pool = $pool;
    }
    /**
     * @return int
     */
    public function getRuleSetSize()
    {
        return \count($this->rules);
    }
    public function getPool()
    {
        return $this->pool;
    }
    // aka solver_makeruledecisions
    private function makeAssertionRuleDecisions()
    {
        $decisionStart = \count($this->decisions) - 1;
        $rulesCount = \count($this->rules);
        for ($ruleIndex = 0; $ruleIndex < $rulesCount; $ruleIndex++) {
            $rule = $this->rules->ruleById[$ruleIndex];
            if (!$rule->isAssertion() || $rule->isDisabled()) {
                continue;
            }
            $literals = $rule->getLiterals();
            $literal = $literals[0];
            if (!$this->decisions->decided($literal)) {
                $this->decisions->decide($literal, 1, $rule);
                continue;
            }
            if ($this->decisions->satisfy($literal)) {
                continue;
            }
            // found a conflict
            if (\RectorPrefix20210503\Composer\DependencyResolver\RuleSet::TYPE_LEARNED === $rule->getType()) {
                $rule->disable();
                continue;
            }
            $conflict = $this->decisions->decisionRule($literal);
            if ($conflict && \RectorPrefix20210503\Composer\DependencyResolver\RuleSet::TYPE_PACKAGE === $conflict->getType()) {
                $problem = new \RectorPrefix20210503\Composer\DependencyResolver\Problem();
                $problem->addRule($rule);
                $problem->addRule($conflict);
                $rule->disable();
                $this->problems[] = $problem;
                continue;
            }
            // conflict with another root require/fixed package
            $problem = new \RectorPrefix20210503\Composer\DependencyResolver\Problem();
            $problem->addRule($rule);
            $problem->addRule($conflict);
            // push all of our rules (can only be root require/fixed package rules)
            // asserting this literal on the problem stack
            foreach ($this->rules->getIteratorFor(\RectorPrefix20210503\Composer\DependencyResolver\RuleSet::TYPE_REQUEST) as $assertRule) {
                if ($assertRule->isDisabled() || !$assertRule->isAssertion()) {
                    continue;
                }
                $assertRuleLiterals = $assertRule->getLiterals();
                $assertRuleLiteral = $assertRuleLiterals[0];
                if (\abs($literal) !== \abs($assertRuleLiteral)) {
                    continue;
                }
                $problem->addRule($assertRule);
                $assertRule->disable();
            }
            $this->problems[] = $problem;
            $this->decisions->resetToOffset($decisionStart);
            $ruleIndex = -1;
        }
    }
    protected function setupFixedMap(\RectorPrefix20210503\Composer\DependencyResolver\Request $request)
    {
        $this->fixedMap = array();
        foreach ($request->getFixedPackages() as $package) {
            $this->fixedMap[$package->id] = $package;
        }
    }
    /**
     * @param Request    $request
     * @param bool|array $ignorePlatformReqs
     */
    protected function checkForRootRequireProblems(\RectorPrefix20210503\Composer\DependencyResolver\Request $request, $ignorePlatformReqs)
    {
        foreach ($request->getRequires() as $packageName => $constraint) {
            if ((\true === $ignorePlatformReqs || \is_array($ignorePlatformReqs) && \in_array($packageName, $ignorePlatformReqs, \true)) && \RectorPrefix20210503\Composer\Repository\PlatformRepository::isPlatformPackage($packageName)) {
                continue;
            }
            if (!$this->pool->whatProvides($packageName, $constraint)) {
                $problem = new \RectorPrefix20210503\Composer\DependencyResolver\Problem();
                $problem->addRule(new \RectorPrefix20210503\Composer\DependencyResolver\GenericRule(array(), \RectorPrefix20210503\Composer\DependencyResolver\Rule::RULE_ROOT_REQUIRE, array('packageName' => $packageName, 'constraint' => $constraint)));
                $this->problems[] = $problem;
            }
        }
    }
    /**
     * @param  Request         $request
     * @param  bool|array      $ignorePlatformReqs
     * @return LockTransaction
     */
    public function solve(\RectorPrefix20210503\Composer\DependencyResolver\Request $request, $ignorePlatformReqs = \false)
    {
        $this->setupFixedMap($request);
        $this->io->writeError('Generating rules', \true, \RectorPrefix20210503\Composer\IO\IOInterface::DEBUG);
        $ruleSetGenerator = new \RectorPrefix20210503\Composer\DependencyResolver\RuleSetGenerator($this->policy, $this->pool);
        $this->rules = $ruleSetGenerator->getRulesFor($request, $ignorePlatformReqs);
        unset($ruleSetGenerator);
        $this->checkForRootRequireProblems($request, $ignorePlatformReqs);
        $this->decisions = new \RectorPrefix20210503\Composer\DependencyResolver\Decisions($this->pool);
        $this->watchGraph = new \RectorPrefix20210503\Composer\DependencyResolver\RuleWatchGraph();
        foreach ($this->rules as $rule) {
            $this->watchGraph->insert(new \RectorPrefix20210503\Composer\DependencyResolver\RuleWatchNode($rule));
        }
        /* make decisions based on root require/fix assertions */
        $this->makeAssertionRuleDecisions();
        $this->io->writeError('Resolving dependencies through SAT', \true, \RectorPrefix20210503\Composer\IO\IOInterface::DEBUG);
        $before = \microtime(\true);
        $this->runSat();
        $this->io->writeError('', \true, \RectorPrefix20210503\Composer\IO\IOInterface::DEBUG);
        $this->io->writeError(\sprintf('Dependency resolution completed in %.3f seconds', \microtime(\true) - $before), \true, \RectorPrefix20210503\Composer\IO\IOInterface::VERBOSE);
        if ($this->problems) {
            throw new \RectorPrefix20210503\Composer\DependencyResolver\SolverProblemsException($this->problems, $this->learnedPool);
        }
        return new \RectorPrefix20210503\Composer\DependencyResolver\LockTransaction($this->pool, $request->getPresentMap(), $request->getFixedPackagesMap(), $this->decisions);
    }
    /**
     * Makes a decision and propagates it to all rules.
     *
     * Evaluates each term affected by the decision (linked through watches)
     * If we find unit rules we make new decisions based on them
     *
     * @param  int       $level
     * @return Rule|null A rule on conflict, otherwise null.
     */
    protected function propagate($level)
    {
        while ($this->decisions->validOffset($this->propagateIndex)) {
            $decision = $this->decisions->atOffset($this->propagateIndex);
            $conflict = $this->watchGraph->propagateLiteral($decision[\RectorPrefix20210503\Composer\DependencyResolver\Decisions::DECISION_LITERAL], $level, $this->decisions);
            $this->propagateIndex++;
            if ($conflict) {
                return $conflict;
            }
        }
        return null;
    }
    /**
     * Reverts a decision at the given level.
     *
     * @param int $level
     */
    private function revert($level)
    {
        while (!$this->decisions->isEmpty()) {
            $literal = $this->decisions->lastLiteral();
            if ($this->decisions->undecided($literal)) {
                break;
            }
            $decisionLevel = $this->decisions->decisionLevel($literal);
            if ($decisionLevel <= $level) {
                break;
            }
            $this->decisions->revertLast();
            $this->propagateIndex = \count($this->decisions);
        }
        while (!empty($this->branches) && $this->branches[\count($this->branches) - 1][self::BRANCH_LEVEL] >= $level) {
            \array_pop($this->branches);
        }
    }
    /**
     * setpropagatelearn
     *
     * add free decision (a positive literal) to decision queue
     * increase level and propagate decision
     * return if no conflict.
     *
     * in conflict case, analyze conflict rule, add resulting
     * rule to learnt rule set, make decision from learnt
     * rule (always unit) and re-propagate.
     *
     * returns the new solver level or 0 if unsolvable
     *
     * @param  int        $level
     * @param  string|int $literal
     * @param  Rule       $rule
     * @return int
     */
    private function setPropagateLearn($level, $literal, \RectorPrefix20210503\Composer\DependencyResolver\Rule $rule)
    {
        $level++;
        $this->decisions->decide($literal, $level, $rule);
        while (\true) {
            $rule = $this->propagate($level);
            if (!$rule) {
                break;
            }
            if ($level == 1) {
                return $this->analyzeUnsolvable($rule);
            }
            // conflict
            list($learnLiteral, $newLevel, $newRule, $why) = $this->analyze($level, $rule);
            if ($newLevel <= 0 || $newLevel >= $level) {
                throw new \RectorPrefix20210503\Composer\DependencyResolver\SolverBugException("Trying to revert to invalid level " . (int) $newLevel . " from level " . (int) $level . ".");
            }
            if (!$newRule) {
                throw new \RectorPrefix20210503\Composer\DependencyResolver\SolverBugException("No rule was learned from analyzing {$rule} at level {$level}.");
            }
            $level = $newLevel;
            $this->revert($level);
            $this->rules->add($newRule, \RectorPrefix20210503\Composer\DependencyResolver\RuleSet::TYPE_LEARNED);
            $this->learnedWhy[\spl_object_hash($newRule)] = $why;
            $ruleNode = new \RectorPrefix20210503\Composer\DependencyResolver\RuleWatchNode($newRule);
            $ruleNode->watch2OnHighest($this->decisions);
            $this->watchGraph->insert($ruleNode);
            $this->decisions->decide($learnLiteral, $level, $newRule);
        }
        return $level;
    }
    /**
     * @param  int   $level
     * @param  array $decisionQueue
     * @param  Rule  $rule
     * @return int
     */
    private function selectAndInstall($level, array $decisionQueue, \RectorPrefix20210503\Composer\DependencyResolver\Rule $rule)
    {
        // choose best package to install from decisionQueue
        $literals = $this->policy->selectPreferredPackages($this->pool, $decisionQueue, $rule->getRequiredPackage());
        $selectedLiteral = \array_shift($literals);
        // if there are multiple candidates, then branch
        if (\count($literals)) {
            $this->branches[] = array($literals, $level);
        }
        return $this->setPropagateLearn($level, $selectedLiteral, $rule);
    }
    /**
     * @param  int   $level
     * @param  Rule  $rule
     * @return array
     */
    protected function analyze($level, \RectorPrefix20210503\Composer\DependencyResolver\Rule $rule)
    {
        $analyzedRule = $rule;
        $ruleLevel = 1;
        $num = 0;
        $l1num = 0;
        $seen = array();
        $learnedLiterals = array(null);
        $decisionId = \count($this->decisions);
        $this->learnedPool[] = array();
        while (\true) {
            $this->learnedPool[\count($this->learnedPool) - 1][] = $rule;
            foreach ($rule->getLiterals() as $literal) {
                // multiconflictrule is really a bunch of rules in one, so some may not have finished propagating yet
                if ($rule instanceof \RectorPrefix20210503\Composer\DependencyResolver\MultiConflictRule && !$this->decisions->decided($literal)) {
                    continue;
                }
                // skip the one true literal
                if ($this->decisions->satisfy($literal)) {
                    continue;
                }
                if (isset($seen[\abs($literal)])) {
                    continue;
                }
                $seen[\abs($literal)] = \true;
                $l = $this->decisions->decisionLevel($literal);
                if (1 === $l) {
                    $l1num++;
                } elseif ($level === $l) {
                    $num++;
                } else {
                    // not level1 or conflict level, add to new rule
                    $learnedLiterals[] = $literal;
                    if ($l > $ruleLevel) {
                        $ruleLevel = $l;
                    }
                }
            }
            unset($literal);
            $l1retry = \true;
            while ($l1retry) {
                $l1retry = \false;
                if (!$num && !--$l1num) {
                    // all level 1 literals done
                    break 2;
                }
                while (\true) {
                    if ($decisionId <= 0) {
                        throw new \RectorPrefix20210503\Composer\DependencyResolver\SolverBugException("Reached invalid decision id {$decisionId} while looking through {$rule} for a literal present in the analyzed rule {$analyzedRule}.");
                    }
                    $decisionId--;
                    $decision = $this->decisions->atOffset($decisionId);
                    $literal = $decision[\RectorPrefix20210503\Composer\DependencyResolver\Decisions::DECISION_LITERAL];
                    if (isset($seen[\abs($literal)])) {
                        break;
                    }
                }
                unset($seen[\abs($literal)]);
                if ($num && 0 === --$num) {
                    if ($literal < 0) {
                        $this->testFlagLearnedPositiveLiteral = \true;
                    }
                    $learnedLiterals[0] = -$literal;
                    if (!$l1num) {
                        break 2;
                    }
                    foreach ($learnedLiterals as $i => $learnedLiteral) {
                        if ($i !== 0) {
                            unset($seen[\abs($learnedLiteral)]);
                        }
                    }
                    // only level 1 marks left
                    $l1num++;
                    $l1retry = \true;
                } else {
                    $decision = $this->decisions->atOffset($decisionId);
                    $rule = $decision[\RectorPrefix20210503\Composer\DependencyResolver\Decisions::DECISION_REASON];
                    if ($rule instanceof \RectorPrefix20210503\Composer\DependencyResolver\MultiConflictRule) {
                        // there is only ever exactly one positive decision in a multiconflict rule
                        foreach ($rule->getLiterals() as $literal) {
                            if (!isset($seen[\abs($literal)]) && $this->decisions->satisfy(-$literal)) {
                                $this->learnedPool[\count($this->learnedPool) - 1][] = $rule;
                                $l = $this->decisions->decisionLevel($literal);
                                if (1 === $l) {
                                    $l1num++;
                                } elseif ($level === $l) {
                                    $num++;
                                } else {
                                    // not level1 or conflict level, add to new rule
                                    $learnedLiterals[] = $literal;
                                    if ($l > $ruleLevel) {
                                        $ruleLevel = $l;
                                    }
                                }
                                $seen[\abs($literal)] = \true;
                                break;
                            }
                        }
                        $l1retry = \true;
                    }
                }
            }
            $decision = $this->decisions->atOffset($decisionId);
            $rule = $decision[\RectorPrefix20210503\Composer\DependencyResolver\Decisions::DECISION_REASON];
        }
        $why = \count($this->learnedPool) - 1;
        if (!$learnedLiterals[0]) {
            throw new \RectorPrefix20210503\Composer\DependencyResolver\SolverBugException("Did not find a learnable literal in analyzed rule {$analyzedRule}.");
        }
        $newRule = new \RectorPrefix20210503\Composer\DependencyResolver\GenericRule($learnedLiterals, \RectorPrefix20210503\Composer\DependencyResolver\Rule::RULE_LEARNED, $why);
        return array($learnedLiterals[0], $ruleLevel, $newRule, $why);
    }
    private function analyzeUnsolvableRule(\RectorPrefix20210503\Composer\DependencyResolver\Problem $problem, \RectorPrefix20210503\Composer\DependencyResolver\Rule $conflictRule, array &$ruleSeen)
    {
        $why = \spl_object_hash($conflictRule);
        $ruleSeen[$why] = \true;
        if ($conflictRule->getType() == \RectorPrefix20210503\Composer\DependencyResolver\RuleSet::TYPE_LEARNED) {
            $learnedWhy = $this->learnedWhy[$why];
            $problemRules = $this->learnedPool[$learnedWhy];
            foreach ($problemRules as $problemRule) {
                if (!isset($ruleSeen[\spl_object_hash($problemRule)])) {
                    $this->analyzeUnsolvableRule($problem, $problemRule, $ruleSeen);
                }
            }
            return;
        }
        if ($conflictRule->getType() == \RectorPrefix20210503\Composer\DependencyResolver\RuleSet::TYPE_PACKAGE) {
            // package rules cannot be part of a problem
            return;
        }
        $problem->nextSection();
        $problem->addRule($conflictRule);
    }
    /**
     * @param  Rule $conflictRule
     * @return int
     */
    private function analyzeUnsolvable(\RectorPrefix20210503\Composer\DependencyResolver\Rule $conflictRule)
    {
        $problem = new \RectorPrefix20210503\Composer\DependencyResolver\Problem();
        $problem->addRule($conflictRule);
        $ruleSeen = array();
        $this->analyzeUnsolvableRule($problem, $conflictRule, $ruleSeen);
        $this->problems[] = $problem;
        $seen = array();
        $literals = $conflictRule->getLiterals();
        foreach ($literals as $literal) {
            // skip the one true literal
            if ($this->decisions->satisfy($literal)) {
                continue;
            }
            $seen[\abs($literal)] = \true;
        }
        foreach ($this->decisions as $decision) {
            $literal = $decision[\RectorPrefix20210503\Composer\DependencyResolver\Decisions::DECISION_LITERAL];
            // skip literals that are not in this rule
            if (!isset($seen[\abs($literal)])) {
                continue;
            }
            $why = $decision[\RectorPrefix20210503\Composer\DependencyResolver\Decisions::DECISION_REASON];
            $problem->addRule($why);
            $this->analyzeUnsolvableRule($problem, $why, $ruleSeen);
            $literals = $why->getLiterals();
            foreach ($literals as $literal) {
                // skip the one true literal
                if ($this->decisions->satisfy($literal)) {
                    continue;
                }
                $seen[\abs($literal)] = \true;
            }
        }
        return 0;
    }
    /**
     * enable/disable learnt rules
     *
     * we have enabled or disabled some of our rules. We now re-enable all
     * of our learnt rules except the ones that were learnt from rules that
     * are now disabled.
     */
    private function enableDisableLearnedRules()
    {
        foreach ($this->rules->getIteratorFor(\RectorPrefix20210503\Composer\DependencyResolver\RuleSet::TYPE_LEARNED) as $rule) {
            $why = $this->learnedWhy[\spl_object_hash($rule)];
            $problemRules = $this->learnedPool[$why];
            $foundDisabled = \false;
            foreach ($problemRules as $problemRule) {
                if ($problemRule->isDisabled()) {
                    $foundDisabled = \true;
                    break;
                }
            }
            if ($foundDisabled && $rule->isEnabled()) {
                $rule->disable();
            } elseif (!$foundDisabled && $rule->isDisabled()) {
                $rule->enable();
            }
        }
    }
    private function runSat()
    {
        $this->propagateIndex = 0;
        /*
         * here's the main loop:
         * 1) propagate new decisions (only needed once)
         * 2) fulfill root requires/fixed packages
         * 3) fulfill all unresolved rules
         * 4) minimalize solution if we had choices
         * if we encounter a problem, we rewind to a safe level and restart
         * with step 1
         */
        $level = 1;
        $systemLevel = $level + 1;
        while (\true) {
            if (1 === $level) {
                $conflictRule = $this->propagate($level);
                if (null !== $conflictRule) {
                    if ($this->analyzeUnsolvable($conflictRule)) {
                        continue;
                    }
                    return;
                }
            }
            // handle root require/fixed package rules
            if ($level < $systemLevel) {
                $iterator = $this->rules->getIteratorFor(\RectorPrefix20210503\Composer\DependencyResolver\RuleSet::TYPE_REQUEST);
                foreach ($iterator as $rule) {
                    if ($rule->isEnabled()) {
                        $decisionQueue = array();
                        $noneSatisfied = \true;
                        foreach ($rule->getLiterals() as $literal) {
                            if ($this->decisions->satisfy($literal)) {
                                $noneSatisfied = \false;
                                break;
                            }
                            if ($literal > 0 && $this->decisions->undecided($literal)) {
                                $decisionQueue[] = $literal;
                            }
                        }
                        if ($noneSatisfied && \count($decisionQueue)) {
                            // if any of the options in the decision queue are fixed, only use those
                            $prunedQueue = array();
                            foreach ($decisionQueue as $literal) {
                                if (isset($this->fixedMap[\abs($literal)])) {
                                    $prunedQueue[] = $literal;
                                }
                            }
                            if (!empty($prunedQueue)) {
                                $decisionQueue = $prunedQueue;
                            }
                        }
                        if ($noneSatisfied && \count($decisionQueue)) {
                            $oLevel = $level;
                            $level = $this->selectAndInstall($level, $decisionQueue, $rule);
                            if (0 === $level) {
                                return;
                            }
                            if ($level <= $oLevel) {
                                break;
                            }
                        }
                    }
                }
                $systemLevel = $level + 1;
                // root requires/fixed packages left
                $iterator->next();
                if ($iterator->valid()) {
                    continue;
                }
            }
            if ($level < $systemLevel) {
                $systemLevel = $level;
            }
            $rulesCount = \count($this->rules);
            $pass = 1;
            $this->io->writeError('Looking at all rules.', \true, \RectorPrefix20210503\Composer\IO\IOInterface::DEBUG);
            for ($i = 0, $n = 0; $n < $rulesCount; $i++, $n++) {
                if ($i == $rulesCount) {
                    if (1 === $pass) {
                        $this->io->writeError("Something's changed, looking at all rules again (pass #{$pass})", \false, \RectorPrefix20210503\Composer\IO\IOInterface::DEBUG);
                    } else {
                        $this->io->overwriteError("Something's changed, looking at all rules again (pass #{$pass})", \false, null, \RectorPrefix20210503\Composer\IO\IOInterface::DEBUG);
                    }
                    $i = 0;
                    $pass++;
                }
                $rule = $this->rules->ruleById[$i];
                $literals = $rule->getLiterals();
                if ($rule->isDisabled()) {
                    continue;
                }
                $decisionQueue = array();
                // make sure that
                // * all negative literals are installed
                // * no positive literal is installed
                // i.e. the rule is not fulfilled and we
                // just need to decide on the positive literals
                //
                foreach ($literals as $literal) {
                    if ($literal <= 0) {
                        if (!$this->decisions->decidedInstall($literal)) {
                            continue 2;
                            // next rule
                        }
                    } else {
                        if ($this->decisions->decidedInstall($literal)) {
                            continue 2;
                            // next rule
                        }
                        if ($this->decisions->undecided($literal)) {
                            $decisionQueue[] = $literal;
                        }
                    }
                }
                // need to have at least 2 item to pick from
                if (\count($decisionQueue) < 2) {
                    continue;
                }
                $level = $this->selectAndInstall($level, $decisionQueue, $rule);
                if (0 === $level) {
                    return;
                }
                // something changed, so look at all rules again
                $rulesCount = \count($this->rules);
                $n = -1;
            }
            if ($level < $systemLevel) {
                continue;
            }
            // minimization step
            if (\count($this->branches)) {
                $lastLiteral = null;
                $lastLevel = null;
                $lastBranchIndex = 0;
                $lastBranchOffset = 0;
                for ($i = \count($this->branches) - 1; $i >= 0; $i--) {
                    list($literals, $l) = $this->branches[$i];
                    foreach ($literals as $offset => $literal) {
                        if ($literal && $literal > 0 && $this->decisions->decisionLevel($literal) > $l + 1) {
                            $lastLiteral = $literal;
                            $lastBranchIndex = $i;
                            $lastBranchOffset = $offset;
                            $lastLevel = $l;
                        }
                    }
                }
                if ($lastLiteral) {
                    unset($this->branches[$lastBranchIndex][self::BRANCH_LITERALS][$lastBranchOffset]);
                    $level = $lastLevel;
                    $this->revert($level);
                    $why = $this->decisions->lastReason();
                    $level = $this->setPropagateLearn($level, $lastLiteral, $why);
                    if ($level == 0) {
                        return;
                    }
                    continue;
                }
            }
            break;
        }
    }
}
