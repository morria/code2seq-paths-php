<?php

declare(strict_types=1);

namespace Paths;

use ast\Node;
use Paths\GraphNode;
use Paths\GraphNode\NonTerminal;
use Paths\GraphNode\Terminal;
use Paths\AST\Visitor\KindVisitorImplementation;
use Paths\AST\Visitor\Element;
use Paths\NodeNameVisitor;

class GraphNodeVisitor extends KindVisitorImplementation
{
    private ?GraphNode $parent = null;

    public function __construct(GraphNode $parent = null)
    {
        $this->parent = $parent;
    }

    public function visit(Node $node): GraphNode
    {
        if (count($node->children) == 0) {
            return new Terminal(self::ELEMENT_NAMES[$node->kind] ?? 'Unknown', $this->parent);
        }

        $gn = new NonTerminal(self::ELEMENT_NAMES[$node->kind] ?? 'Unknown', $this->parent);
        foreach ($node->children as $child) {
            if ($child instanceof Node) {
                $gn->appendChild((new GraphNodeVisitor($gn))($child));
            } else {
                $gn->appendChild(new Terminal("{$child}", $gn));
            }
        }
        return $gn;
    }

    public function visitVar(Node $node): GraphNode
    {
        $gn = new NonTerminal("Variable", $this->parent);
        $gn->appendChild(new Terminal($node->children['name'], $gn));
        return $gn;
    }

    public function visitFuncDecl(Node $node): GraphNode
    {
        $gn = new NonTerminal("Function", $this->parent);

        // Name
        $gn->appendChild(new Terminal($node->children['name'], $gn));

        // Parameters
        foreach ($node->children['params']->children as $param) {
            if ($param instanceof Node) {
                $gn->appendChild((new GraphNodeVisitor($gn))($param));
            } else {
                $gn->appendChild(new Terminal("{$param}", $gn));
            }
        }

        // Return type
        // TODO: ...

        // Body
        foreach ($node->children['stmts']->children as $stmt) {
            $gn->appendChild((new GraphNodeVisitor($gn))($stmt));
        }

        return $gn;
    }

    public function visitParam(Node $node): GraphNode
    {
        $gn = new NonTerminal("Parameter", $this->parent);

        // Type
        if (isset($node->children['type'])) {
            $gn->appendChild((new GraphNodeVisitor($gn))($node->children['type']));
        }

        // Name
        if (isset($node->children['name'])) {
            $gn->appendChild(new Terminal($node->children['name'], $gn));
        }

        // Default
        // TODO: ...

        return $gn;
    }

    public function visitType(Node $node): GraphNode
    {
        $map = [
            \ast\flags\TYPE_ARRAY => 'array',
            \ast\flags\TYPE_BOOL => 'bool',
            \ast\flags\TYPE_CALLABLE => 'callable',
            \ast\flags\TYPE_DOUBLE => 'double',
            \ast\flags\TYPE_ITERABLE  => 'iterable',
            \ast\flags\TYPE_LONG => 'long',
            \ast\flags\TYPE_NULL => 'null',
            \ast\flags\TYPE_OBJECT => 'object',
            \ast\flags\TYPE_STRING => 'string',
            \ast\flags\TYPE_VOID => 'void',
            \ast\flags\TYPE_FALSE => 'false',
            \ast\flags\TYPE_TRUE => 'true',
            \ast\flags\TYPE_STATIC => 'static',
            \ast\flags\TYPE_MIXED => 'mixed',
            \ast\flags\TYPE_NEVER => 'never',
        ];
        return new Terminal($map[$node->flags] ?? "unknown", $this->parent);
    }

    public function visitStmtList(Node $node): GraphNode
    {
        $gn = new NonTerminal("StatementList", $this->parent);
        foreach ($node->children as $child) {
            $gn->appendChild((new GraphNodeVisitor($gn))($child));
        }
        return $gn;
    }

    public function visitForeach(Node $node): GraphNode
    {
        $gn = new NonTerminal("Foreach", $this->parent);
        $gn->appendChild((new GraphNodeVisitor($gn))($node->children['expr']));

        $gn->appendChild((new GraphNodeVisitor($gn))($node->children['value']));

        if (isset($node->children['key'])) {
            $gn->appendChild((new GraphNodeVisitor($gn))($node->children['key']));
        }

        $gn->appendChild((new GraphNodeVisitor($gn))($node->children['stmts']));

        return $gn;
    }

    public function visitIf(Node $node): GraphNode
    {
        $gn = new NonTerminal("If", $this->parent);

        foreach ($node->children as $child) {
            $gn->appendChild((new GraphNodeVisitor($gn))($child));
        }

        return $gn;
    }

    public function visitIfElem(Node $node)
    {
        $gn = new NonTerminal("IfElem", $this->parent);
        $gn->appendChild((new GraphNodeVisitor($gn))($node->children['cond']));
        $gn->appendChild((new GraphNodeVisitor($gn))($node->children['stmts']));
        return $gn;
    }


    /**
     * Accepts a visitor that differentiates on the kind value
     * of the AST node.
     *
     * NOTE: This was turned into a static method for performance
     * because it was called extremely frequently.
     *
     * @return mixed - The type depends on the subclass of KindVisitor being used.
     * @suppress PhanUnreferencedPublicMethod Phan's code inlines this, but may be useful for some plugins
     */
    public static function acceptNodeAndKindVisitor(Node $node, KindVisitor $visitor)
    {
        $fn_name = self::VISIT_LOOKUP_TABLE[$node->kind] ?? null;
        if (\is_string($fn_name)) {
            return $visitor->{$fn_name}($node);
        } else {
            Debug::printNode($node);
            throw new AssertionError('All node kinds must match');
        }
    }

    /*
    public const BINARY_OP_NAMES = [
        252 => 'BinaryConcat',
        flags\BINARY_ADD => 'BinaryAdd',
        flags\BINARY_BITWISE_AND => 'BinaryBitwiseAnd',
        flags\BINARY_BITWISE_OR => 'BinaryBitwiseOr',
        flags\BINARY_BITWISE_XOR => 'BinaryBitwiseXor',
        flags\BINARY_BOOL_XOR => 'BinaryBoolXor',
        flags\BINARY_CONCAT => 'BinaryConcat',
        flags\BINARY_DIV => 'BinaryDiv',
        flags\BINARY_IS_EQUAL => 'BinaryIsEqual',
        flags\BINARY_IS_IDENTICAL => 'BinaryIsIdentical',
        flags\BINARY_IS_NOT_EQUAL => 'BinaryIsNotEqual',
        flags\BINARY_IS_NOT_IDENTICAL => 'BinaryIsNotIdentical',
        flags\BINARY_IS_SMALLER => 'BinaryIsSmaller',
        flags\BINARY_IS_SMALLER_OR_EQUAL => 'BinaryIsSmallerOrEqual',
        flags\BINARY_MOD => 'BinaryMod',
        flags\BINARY_MUL => 'BinaryMul',
        flags\BINARY_POW => 'BinaryPow',
        flags\BINARY_SHIFT_LEFT => 'BinaryShiftLeft',
        flags\BINARY_SHIFT_RIGHT => 'BinaryShiftRight',
        flags\BINARY_SPACESHIP => 'BinarySpaceship',
        flags\BINARY_SUB => 'BinarySub',
        flags\BINARY_BOOL_AND => 'BinaryBoolAnd',
        flags\BINARY_BOOL_OR => 'BinaryBoolOr',
        flags\BINARY_COALESCE => 'BinaryCoalesce',
        flags\BINARY_IS_GREATER => 'BinaryIsGreater',
        flags\BINARY_IS_GREATER_OR_EQUAL => 'BinaryIsGreaterOrEqual',
    ];
    */

    public const ELEMENT_NAMES = [
        \ast\AST_ARG_LIST => 'ArgList',
        \ast\AST_ARRAY => 'Array',
        \ast\AST_ARRAY_ELEM => 'ArrayElem',
        \ast\AST_ARROW_FUNC => 'ArrowFunc',
        \ast\AST_ASSIGN => 'Assign',
        \ast\AST_ASSIGN_OP => 'AssignOp',
        \ast\AST_ASSIGN_REF => 'AssignRef',
        \ast\AST_ATTRIBUTE => 'Attribute',
        \ast\AST_ATTRIBUTE_LIST => 'AttributeList',
        \ast\AST_ATTRIBUTE_GROUP => 'AttributeGroup',
        \ast\AST_BINARY_OP => 'BinaryOp',
        \ast\AST_BREAK => 'Break',
        \ast\AST_CALL => 'Call',
        \ast\AST_CALLABLE_CONVERT => 'CallableConvert',
        \ast\AST_CAST => 'Cast',
        \ast\AST_CATCH => 'Catch',
        \ast\AST_CLASS => 'Class',
        \ast\AST_CLASS_CONST => 'ClassConst',
        \ast\AST_CLASS_CONST_DECL => 'ClassConstDecl',
        \ast\AST_CLASS_CONST_GROUP => 'ClassConstGroup',
        \ast\AST_CLASS_NAME => 'ClassName',
        \ast\AST_CLOSURE => 'Closure',
        \ast\AST_CLOSURE_USES => 'ClosureUses',
        \ast\AST_CLOSURE_VAR => 'ClosureVar',
        \ast\AST_CONST => 'Const',
        \ast\AST_CONST_DECL => 'ConstDecl',
        \ast\AST_CONST_ELEM => 'ConstElem',
        \ast\AST_DECLARE => 'Declare',
        \ast\AST_DIM => 'Dim',
        \ast\AST_DO_WHILE => 'DoWhile',
        \ast\AST_ECHO => 'Echo',
        \ast\AST_EMPTY => 'Empty',
        \ast\AST_ENCAPS_LIST => 'EncapsList',
        \ast\AST_ENUM_CASE => 'EnumCase',
        \ast\AST_EXIT => 'Exit',
        \ast\AST_EXPR_LIST => 'ExprList',
        \ast\AST_FOREACH => 'Foreach',
        \ast\AST_FUNC_DECL => 'FuncDecl',
        \ast\AST_ISSET => 'Isset',
        \ast\AST_GLOBAL => 'Global',
        \ast\AST_GROUP_USE => 'GroupUse',
        \ast\AST_IF => 'If',
        \ast\AST_IF_ELEM => 'IfElem',
        \ast\AST_INSTANCEOF => 'Instanceof',
        \ast\AST_MAGIC_CONST => 'MagicConst',
        \ast\AST_MATCH => 'Match',
        \ast\AST_MATCH_ARM => 'MatchArm',
        \ast\AST_MATCH_ARM_LIST => 'MatchArmList',
        \ast\AST_METHOD => 'Method',
        \ast\AST_METHOD_CALL => 'MethodCall',
        \ast\AST_NAME => 'Name',
        \ast\AST_NAMED_ARG => 'NamedArg',
        \ast\AST_NAMESPACE => 'Namespace',
        \ast\AST_NEW => 'New',
        \ast\AST_NULLSAFE_METHOD_CALL => 'NullsafeMethodCall',
        \ast\AST_NULLSAFE_PROP => 'NullsafeProp',
        \ast\AST_PARAM => 'Param',
        \ast\AST_PARAM_LIST => 'ParamList',
        \ast\AST_PRE_INC => 'PreInc',
        \ast\AST_PRINT => 'Print',
        \ast\AST_PROP => 'Prop',
        \ast\AST_PROP_DECL => 'PropDecl',
        \ast\AST_PROP_ELEM => 'PropElem',
        \ast\AST_PROP_GROUP => 'PropGroup',
        \ast\AST_RETURN => 'Return',
        \ast\AST_STATIC => 'Static',
        \ast\AST_STATIC_CALL => 'StaticCall',
        \ast\AST_STATIC_PROP => 'StaticProp',
        \ast\AST_STMT_LIST => 'StmtList',
        \ast\AST_SWITCH => 'Switch',
        \ast\AST_SWITCH_CASE => 'SwitchCase',
        \ast\AST_SWITCH_LIST => 'SwitchList',
        \ast\AST_TYPE => 'Type',
        \ast\AST_TYPE_INTERSECTION => 'TypeIntersection',
        \ast\AST_TYPE_UNION => 'TypeUnion',
        \ast\AST_NULLABLE_TYPE => 'NullableType',
        \ast\AST_UNARY_OP => 'UnaryOp',
        \ast\AST_USE => 'Use',
        \ast\AST_USE_ELEM => 'UseElem',
        \ast\AST_USE_TRAIT => 'UseTrait',
        \ast\AST_VAR => 'Var',
        \ast\AST_WHILE => 'While',
        \ast\AST_CATCH_LIST => 'CatchList',
        \ast\AST_CLONE => 'Clone',
        \ast\AST_CONDITIONAL => 'Conditional',
        \ast\AST_CONTINUE => 'Continue',
        \ast\AST_FOR => 'For',
        \ast\AST_GOTO => 'Goto',
        \ast\AST_HALT_COMPILER => 'HaltCompiler',
        \ast\AST_INCLUDE_OR_EVAL => 'IncludeOrEval',
        \ast\AST_LABEL => 'Label',
        \ast\AST_METHOD_REFERENCE => 'MethodReference',
        \ast\AST_NAME_LIST => 'NameList',
        \ast\AST_POST_DEC => 'PostDec',
        \ast\AST_POST_INC => 'PostInc',
        \ast\AST_PRE_DEC => 'PreDec',
        \ast\AST_REF => 'Ref',
        \ast\AST_SHELL_EXEC => 'ShellExec',
        \ast\AST_THROW => 'Throw',
        \ast\AST_TRAIT_ADAPTATIONS => 'TraitAdaptations',
        \ast\AST_TRAIT_ALIAS => 'TraitAlias',
        \ast\AST_TRAIT_PRECEDENCE => 'TraitPrecedence',
        \ast\AST_TRY => 'visitTry',
        \ast\AST_UNPACK => 'Unpack',
        \ast\AST_UNSET => 'Unset',
        \ast\AST_YIELD => 'Yield',
        \ast\AST_YIELD_FROM => 'YieldFrom',
    ];
}
