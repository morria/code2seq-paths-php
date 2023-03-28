<?php

declare(strict_types=1);

namespace Paths;

use ast\Node;
use Paths\GraphNode;
use Paths\GraphNode\NonTerminal;
use Paths\GraphNode\Terminal;
use Paths\AST\Visitor\KindVisitorImplementation;

class GraphNodeVisitor extends KindVisitorImplementation
{
    private ?GraphNode $parent = null;

    public function __construct(GraphNode $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * @param $node Node|null|string|int
     */
    public static function graphNodeFromNodeOrValue(mixed $node, GraphNode $parent): GraphNode
    {
        if ($node instanceof Node) {
            return (new GraphNodeVisitor($parent))($node);
        }
        return self::terminalFromNodeOrValue($node, $parent);
    }

    /**
     * @param $node Node|null|string|int
     */
    public static function terminalFromNodeOrValue(mixed $node, GraphNode $parent): Terminal
    {
        if ($node instanceof Node) {
            return new Terminal(self::ELEMENT_NAMES[$node->kind] ?? 'Unknown', $parent);
        }

        $name = $node === null ? 'null' : "$node";
        return new Terminal($name, $parent);
    }

    /**
     * Default visitor which attempts to create a reasonable GraphNode
     * from the given AST node.
     */
    public function visit(Node $node): GraphNode
    {
        if (count($node->children) == 0) {
            return self::terminalFromNodeOrValue($node, $this->parent);
        }

        $gn = new NonTerminal(self::ELEMENT_NAMES[$node->kind] ?? 'Unknown', $this->parent);
        foreach ($node->children as $child) {
            $gn->appendChild(self::graphNodeFromNodeOrValue($child, $gn));
        }
        return $gn;
    }

    public function visitVar(Node $node): GraphNode
    {
        $gn = new NonTerminal("Variable", $this->parent);
        $gn->appendChild(self::terminalFromNodeOrValue($node->children['name'], $gn));
        return $gn;
    }

    public function visitMethod(Node $node): GraphNode
    {
        return $this->visitFuncDecl($node);
    }

    public function visitFuncDecl(Node $node): GraphNode
    {
        $gn = new NonTerminal("Function", $this->parent);

        $gn->appendChild(self::terminalFromNodeOrValue($node->children['name'], $gn));

        foreach ($node->children['params']->children ?? [] as $param) {
            $gn->appendChild(self::graphNodeFromNodeOrValue($param, $gn));
        }

        foreach ($node->children['stmts']->children ?? [] as $stmt) {
            $gn->appendChild((new GraphNodeVisitor($gn))($stmt));
        }

        // TODO: Return type

        return $gn;
    }

    public function visitParamList(Node $node): GraphNode
    {
        if (count($node->children) == 0) {
            return self::terminalFromNodeOrValue('EmptyParameters', $this->parent);
        }

        $gn = new NonTerminal("ParameterList", $this->parent);
        foreach ($node->children ?? [] as $param) {
            $gn->appendChild((new GraphNodeVisitor($gn))($param));
        }
        return $gn;
    }

    public function visitParam(Node $node): GraphNode
    {
        $gn = new NonTerminal("Parameter", $this->parent);

        $type = $node->children['type'] ?? null;
        $gn->appendChild(self::graphNodeFromNodeOrValue($type, $gn));
        $gn->appendChild(self::terminalFromNodeOrValue($node->children['name'], $gn));

        // TODO: Default value

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
        return new Terminal($map[$node->flags] ?? "Unknown", $this->parent);
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

        $expr = $node->children['expr'] ?? null;
        if ($expr instanceof Node) {
            $gn->appendChild((new GraphNodeVisitor($gn))($expr));
        }

        $value = $node->children['value'] ?? null;
        if ($value instanceof Node) {
            $gn->appendChild((new GraphNodeVisitor($gn))($value));
        }

        $key = $node->children['key'] ?? null;
        if ($key instanceof Node) {
            $gn->appendChild((new GraphNodeVisitor($gn))($key));
        }

        $stmts = $node->children['stmts'] ?? null;
        if ($stmts instanceof Node) {
            $gn->appendChild((new GraphNodeVisitor($gn))($stmts));
        }

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
        $gn = new NonTerminal("IfElement", $this->parent);

        $cond = $node->children['cond'] ?? null;
        if ($cond instanceof Node) {
            $gn->appendChild(self::graphNodeFromNodeOrValue($cond, $gn));
        }

        $stmts = $node->children['stmts'] ?? null;
        if ($stmts instanceof Node) {
            $gn->appendChild(self::graphNodeFromNodeOrValue($stmts, $gn));
        }

        return $gn;
    }

    public function visitName(Node $node): GraphNode
    {
        return self::terminalFromNodeOrValue($node->children['name'], $this->parent);
    }

    public function visitNew(Node $node): GraphNode
    {
        $gn = new NonTerminal("New", $this->parent);

        $class = $node->children['class'] ?? null;
        if ($class instanceof Node) {
            $gn->appendChild(self::graphNodeFromNodeOrValue($class, $gn));
        }

        $args = $node->children['args'] ?? null;
        if ($args instanceof Node && count($args->children) > 0) {
            $gn->appendChild(self::graphNodeFromNodeOrValue($args, $gn));
        }

        return $gn;
    }

    public function visitArgList(Node $node): GraphNode
    {
        $gn = new NonTerminal("ArgumentList", $this->parent);
        foreach ($node->children as $child) {
            $gn->appendChild(self::graphNodeFromNodeOrValue($child, $gn));
        }
        return $gn;
    }

    public function visitBinaryOp(Node $node)
    {
        $gn = new NonTerminal(self::BINARY_OP_NAMES[$node->flags] ?? 'BinaryOp', $this->parent);
        $gn->appendChild(self::graphNodeFromNodeOrValue($node->children['left'], $gn));
        $gn->appendChild(self::graphNodeFromNodeOrValue($node->children['right'], $gn));
        return $gn;
    }

    public const BINARY_OP_NAMES = [
        252 => 'BinaryConcat',
        \ast\flags\BINARY_ADD => 'Add',
        \ast\flags\BINARY_BITWISE_AND => 'BitwiseAnd',
        \ast\flags\BINARY_BITWISE_OR => 'BitwiseOr',
        \ast\flags\BINARY_BITWISE_XOR => 'BitwiseXor',
        \ast\flags\BINARY_BOOL_XOR => 'BoolXor',
        \ast\flags\BINARY_CONCAT => 'Concat',
        \ast\flags\BINARY_DIV => 'Div',
        \ast\flags\BINARY_IS_EQUAL => 'IsEqual',
        \ast\flags\BINARY_IS_IDENTICAL => 'IsIdentical',
        \ast\flags\BINARY_IS_NOT_EQUAL => 'IsNotEqual',
        \ast\flags\BINARY_IS_NOT_IDENTICAL => 'IsNotIdentical',
        \ast\flags\BINARY_IS_SMALLER => 'IsSmaller',
        \ast\flags\BINARY_IS_SMALLER_OR_EQUAL => 'IsSmallerOrEqual',
        \ast\flags\BINARY_MOD => 'Mod',
        \ast\flags\BINARY_MUL => 'Multiply',
        \ast\flags\BINARY_POW => 'Power',
        \ast\flags\BINARY_SHIFT_LEFT => 'ShiftLeft',
        \ast\flags\BINARY_SHIFT_RIGHT => 'ShiftRight',
        \ast\flags\BINARY_SPACESHIP => 'Spaceship',
        \ast\flags\BINARY_SUB => 'Subtract',
        \ast\flags\BINARY_BOOL_AND => 'BoolAnd',
        \ast\flags\BINARY_BOOL_OR => 'BoolOr',
        \ast\flags\BINARY_COALESCE => 'Coalesce',
        \ast\flags\BINARY_IS_GREATER => 'IsGreater',
        \ast\flags\BINARY_IS_GREATER_OR_EQUAL => 'IsGreaterOrEqual',
    ];

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
