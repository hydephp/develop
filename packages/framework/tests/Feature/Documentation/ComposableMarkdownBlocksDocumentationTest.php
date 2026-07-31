<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Documentation;

use Hyde\Console\Helpers\ConsoleHelper;
use Hyde\Foundation\Facades\Routes;
use Hyde\Framework\Services\MarkdownService;
use Hyde\Hyde;
use Hyde\Markdown\Contracts\MarkdownPostProcessorContract;
use Hyde\Markdown\Contracts\MarkdownPreProcessorContract;
use Hyde\Markdown\Contracts\MarkdownShortcodeContract;
use Hyde\Markdown\Extensions\Nodes\TerminalBlock;
use Hyde\Markdown\Extensions\TerminalExtension;
use Hyde\Markdown\Models\Markdown;
use Hyde\Markdown\Processing\BladeBlockProcessor;
use Hyde\Markdown\Processing\BladeDownProcessor;
use Hyde\Markdown\Processing\CodeblockFilepathProcessor;
use Hyde\Markdown\Processing\ColoredBlockquotes;
use Hyde\Markdown\Processing\DynamicMarkdownLinkProcessor;
use Hyde\Markdown\Processing\HeadingRenderer;
use Hyde\Markdown\Processing\ShortcodeProcessor;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Support\Models\Route;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use InvalidArgumentException;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use Laravel\Prompts\Prompt;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;
use Torchlight\Commonmark\V2\TorchlightExtension;

use function array_filter;
use function array_map;
use function array_shift;
use function array_slice;
use function array_values;
use function class_implements;
use function config;
use function explode;
use function implode;
use function in_array;
use function iterator_to_array;
use function preg_match;
use function preg_match_all;
use function str_contains;
use function str_replace;
use function strstr;
use function strtolower;
use function substr_count;
use function trim;
use function view;
use function windows_os;

/**
 * Documentation driven tests asserting the accuracy of the composable Markdown blocks documentation page.
 *
 * Every claim the page makes about the framework has a test here, so that a change which makes
 * the documentation inaccurate fails the test suite instead of silently misleading readers.
 *
 * The tests are ordered to follow the page, and each one is named after the claim it asserts.
 *
 * @see docs/digging-deeper/composable-markdown-blocks.md
 */
#[CoversClass(MarkdownService::class)]
#[CoversClass(TerminalExtension::class)]
#[CoversClass(TerminalBlock::class)]
#[CoversClass(\Hyde\Markdown\Extensions\Processing\TerminalBlockRenderer::class)]
#[CoversClass(\Hyde\Markdown\Extensions\Processing\TransformTerminalBlocks::class)]
#[CoversClass(HeadingRenderer::class)]
#[CoversClass(ColoredBlockquotes::class)]
#[CoversClass(ShortcodeProcessor::class)]
#[CoversClass(CodeblockFilepathProcessor::class)]
#[CoversClass(BladeBlockProcessor::class)]
#[CoversClass(\Hyde\Console\Commands\PublishViewsCommand::class)]
class ComposableMarkdownBlocksDocumentationTest extends TestCase
{
    /** The documentation page asserted by this test, relative to the project root. */
    protected const PAGE = 'docs/digging-deeper/composable-markdown-blocks.md';

    protected function tearDown(): void
    {
        ConsoleHelper::clearMocks();
        PromptFallbackReset::resetFallbacks();

        foreach (['resources/views/vendor', 'resources/views/components'] as $directory) {
            if (File::isDirectory(Hyde::path($directory))) {
                File::deleteDirectory(Hyde::path($directory));
            }
        }

        if (File::isDirectory(Hyde::path('resources/views')) && File::isEmptyDirectory(Hyde::path('resources/views'))) {
            File::deleteDirectory(Hyde::path('resources/views'));
        }

        parent::tearDown();
    }

    /*
    |--------------------------------------------------------------------------
    | Introduction, The Idea, and Blocks At a Glance
    |--------------------------------------------------------------------------
    */

    public function testTerminalFencesAreHandedToTheTerminalViewInsteadOfAHardcodedTemplate()
    {
        $this->publishView('vendor/hyde/components/markdown/terminal.blade.php', 'The view decides: {!! $contents !!}');

        $this->assertStringContainsString('The view decides: Building your static site!',
            Markdown::render("```terminal\nBuilding your static site!\n```")
        );
    }

    public function testBlockViewsAreNormalBladeTemplatesWithEverythingBladeOffers()
    {
        $this->publishView('components/documentation-partial.blade.php', 'included');

        $this->publishView('vendor/hyde/components/markdown/terminal.blade.php', <<<'BLADE'
        @if(true)[conditional]@endif
        @foreach(['one', 'two'] as $item)[{{ $item }}]@endforeach
        [@include('components.documentation-partial')]
        [{{ config('hyde.name', 'HydePHP') }}]
        [{!! $contents !!}]
        BLADE);

        $html = Markdown::render("```terminal\nBuilding your static site!\n```");

        $this->assertStringContainsString('[conditional]', $html);
        $this->assertStringContainsString('[one][two]', $html);
        $this->assertStringContainsString('[included]', $html);
        $this->assertStringContainsString('[HydePHP]', $html);
        $this->assertStringContainsString('[Building your static site!', $html);
    }

    public function testEveryDocumentedViewIsShippedByTheFrameworkPackage()
    {
        // "All view paths are relative to resources/views/components/ in the framework package"

        foreach ($this->documentedViews() as $view) {
            $this->assertFileExists($this->frameworkView($view), "The documented view [$view] does not exist.");
        }
    }

    public function testEveryDocumentedViewIsAddressableByItsDocumentedName()
    {
        $views = [
            'hyde::components.markdown.terminal',
            'hyde::components.colored-blockquote',
            'hyde::components.markdown-heading',
            'hyde::components.filepath-label',
        ];

        foreach ($views as $view) {
            $this->assertTrue(View::exists($view), "The documented view [$view] could not be resolved.");
        }
    }

    public function testEachDocumentedBlockUsesItsDocumentedMechanism()
    {
        // Terminal blocks and headings are rendered by the CommonMark renderer
        $this->assertContains(ExtensionInterface::class, class_implements(TerminalExtension::class));
        $this->assertContains(NodeRendererInterface::class, class_implements(HeadingRenderer::class));

        // Coloured blockquotes are expanded by a Markdown pre-processor
        $this->assertContains(MarkdownPreProcessorContract::class, class_implements(ShortcodeProcessor::class));
        $this->assertContains(MarkdownShortcodeContract::class, class_implements(ColoredBlockquotes::class));

        // Filepath labels are injected by a Markdown post-processor (marked up by its pre-processor)
        $this->assertContains(MarkdownPostProcessorContract::class, class_implements(CodeblockFilepathProcessor::class));
        $this->assertContains(MarkdownPreProcessorContract::class, class_implements(CodeblockFilepathProcessor::class));

        // Blade component blocks are extracted by a Markdown pre-processor
        $this->assertContains(MarkdownPreProcessorContract::class, class_implements(BladeBlockProcessor::class));
    }

    /*
    |--------------------------------------------------------------------------
    | Publishing the Views
    |--------------------------------------------------------------------------
    */

    public function testEveryBuiltInBlockViewIsPublishableThroughTheDocumentedCommand()
    {
        ConsoleHelper::mockWindowsOs(true);

        $this->artisan('publish:views components')
            ->expectsOutput('Published all [component] files to [resources/views/vendor/hyde/components]')
            ->assertExitCode(0);

        foreach ($this->documentedViews() as $view) {
            $this->assertFileExists(Hyde::path("resources/views/vendor/hyde/components/$view"));
        }
    }

    public function testRunningThePublishCommandWithoutArgumentsPromptsForAGroupFirst()
    {
        ConsoleHelper::disableLaravelPrompts();

        $this->artisan('publish:views')
            ->expectsQuestion('Which group do you want to publish?', $this->componentsGroupChoice())
            ->expectsOutput('Published all [component] files to [resources/views/vendor/hyde/components]')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('resources/views/vendor/hyde/components/markdown/terminal.blade.php'));
    }

    public function testChoosingAGroupThenAsksWhichIndividualFilesToPublish()
    {
        if (windows_os()) {
            $this->markTestSkipped('Test is not applicable on Windows systems.');
        }

        $this->artisan('publish:views')
            ->expectsQuestion('Which group do you want to publish?', $this->componentsGroupChoice())
            ->expectsOutput('Selected group [components]')
            ->expectsQuestion('Select the files you want to publish', [$this->publishableFilePath('markdown/terminal.blade.php')])
            ->expectsOutput('Published selected file to [resources/views/vendor/hyde/components/markdown/terminal.blade.php]')
            ->assertExitCode(0);

        // Just the one view we cared about, instead of the whole set
        $this->assertFileExists(Hyde::path('resources/views/vendor/hyde/components/markdown/terminal.blade.php'));
        $this->assertFileDoesNotExist(Hyde::path('resources/views/vendor/hyde/components/colored-blockquote.blade.php'));
    }

    public function testChoosingToPublishEverythingDoesNotAskWhichFilesToPublish()
    {
        if (windows_os()) {
            $this->markTestSkipped('Test is not applicable on Windows systems.');
        }

        $this->artisan('publish:views')
            ->expectsQuestion('Which group do you want to publish?', 'Publish all groups listed below')
            ->doesntExpectOutputToContain('Select the files you want to publish')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('resources/views/vendor/hyde/components/markdown/terminal.blade.php'));
    }

    public function testPublishedViewsMirrorTheFrameworksDirectoryStructure()
    {
        ConsoleHelper::mockWindowsOs(true);

        $this->artisan('publish:views components')->assertExitCode(0);

        // The exact tree shown in the documentation
        $this->assertFileExists(Hyde::path('resources/views/vendor/hyde/components/colored-blockquote.blade.php'));
        $this->assertFileExists(Hyde::path('resources/views/vendor/hyde/components/filepath-label.blade.php'));
        $this->assertFileExists(Hyde::path('resources/views/vendor/hyde/components/markdown-heading.blade.php'));
        $this->assertDirectoryExists(Hyde::path('resources/views/vendor/hyde/components/markdown'));
        $this->assertFileExists(Hyde::path('resources/views/vendor/hyde/components/markdown/terminal.blade.php'));
    }

    public function testAPublishedViewTakesPrecedenceOverTheFrameworkCopyWithNothingToRegister()
    {
        $this->assertStringContainsString('border-blue-500', Markdown::render('>info Hello'));

        $this->publishView('vendor/hyde/components/colored-blockquote.blade.php', 'Published blockquote: {!! $contents !!}');

        $html = Markdown::render('>info Hello');

        $this->assertStringContainsString('Published blockquote: <p>Hello</p>', $html);
        $this->assertStringNotContainsString('border-blue-500', $html);
    }

    public function testPublishingOverwritesAnyExistingFileAtTheTargetPath()
    {
        $this->publishView('vendor/hyde/components/colored-blockquote.blade.php', 'My customized view');

        ConsoleHelper::mockWindowsOs(true);

        $this->artisan('publish:views components')->assertExitCode(0);

        $this->assertSame(
            $this->frameworkViewContents('colored-blockquote.blade.php'),
            $this->normalize(File::get(Hyde::path('resources/views/vendor/hyde/components/colored-blockquote.blade.php')))
        );
    }

    public function testTailwindConfigScansThePublishedViewsDirectory()
    {
        if (! File::exists(Hyde::path('tailwind.config.js'))) {
            $this->markTestSkipped('The Tailwind configuration is not present in this installation.');
        }

        $this->assertStringContainsString("'./resources/views/**/*.blade.php'", File::get(Hyde::path('tailwind.config.js')));
    }

    public function testBlockMarkupIncludesStableNonUtilityClassHooksForStylingWithoutPublishing()
    {
        // The CSS example in the documentation targets .hyde-terminal-body
        $this->assertStringContainsString('class="hyde-terminal-body', Markdown::render("```terminal\n\$ php hyde build\n```"));
    }

    /*
    |--------------------------------------------------------------------------
    | The Rendering Pipeline
    |--------------------------------------------------------------------------
    */

    public function testPreProcessorsRunAgainstTheRawMarkdownInTheDocumentedOrder()
    {
        $this->assertSame([
            BladeBlockProcessor::class,
            BladeDownProcessor::class,
            ShortcodeProcessor::class,
            CodeblockFilepathProcessor::class,
        ], $this->readProperty($this->markdownService(), 'preprocessors'));
    }

    public function testPostProcessorsRunAgainstTheResultingHtmlInTheDocumentedOrder()
    {
        $documented = [
            BladeBlockProcessor::class,
            CodeblockFilepathProcessor::class,
            DynamicMarkdownLinkProcessor::class,
        ];

        $registered = $this->readProperty($this->markdownService(), 'postprocessors');

        $this->assertSame($documented, array_values(array_filter($registered,
            fn (string $processor): bool => in_array($processor, $documented, true)
        )));
    }

    public function testBladeBlockProcessorReplacesBlocksWithAPlaceholderCommentAndSwapsThemBackAfterwards()
    {
        $this->publishAlertComponent();

        $markdown = "Intro paragraph.\n\n```blade render\n{{ 'Rendered by Blade' }}\n```";

        $preprocessed = BladeBlockProcessor::preprocess($markdown);

        // Nothing downstream tries to parse the contents of the block
        $this->assertStringNotContainsString('Rendered by Blade', $preprocessed);
        $this->assertStringContainsString('<!-- HYDE[BladeBlock]', $preprocessed);

        // The post-processor swaps the placeholder back out for the rendered Blade output
        $this->assertStringContainsString('Rendered by Blade', BladeBlockProcessor::postprocess($preprocessed));

        // Component blocks are extracted the same way
        $preprocessed = BladeBlockProcessor::preprocess("Intro paragraph.\n\n```blade component=\"alert\"\nSlot content\n```");

        $this->assertStringContainsString('<!-- HYDE[BladeBlock]', $preprocessed);
        $this->assertStringNotContainsString('Slot content', $preprocessed);

        // The processor holds extracted blocks statically until they are swapped back in
        $this->assertStringContainsString('Slot content', BladeBlockProcessor::postprocess($preprocessed));
    }

    public function testBladeDownProcessorHandlesSingleLineBladeDirectives()
    {
        $this->assertStringContainsString('Rendered by Blade', Markdown::render("[Blade]: {{ 'Rendered by Blade' }}"));
    }

    public function testShortcodeProcessorExpandsColouredBlockquotesIntoRenderedHtml()
    {
        $this->assertStringContainsString('<blockquote class="border-blue-500">', ShortcodeProcessor::preprocess('>info Hello'));
    }

    public function testCodeblockFilepathProcessorConvertsFilepathCommentsIntoMarkers()
    {
        $markdown = "Intro paragraph.\n\n```php\n// filepath: hello-world.php\necho 'Hello World!';\n```";

        $preprocessed = CodeblockFilepathProcessor::preprocess($markdown);

        $this->assertStringContainsString('<!-- HYDE[Filepath]hello-world.php -->', $preprocessed);
        $this->assertStringNotContainsString('// filepath:', $preprocessed);

        // The post-processor replaces the marker with the rendered filepath label view
        $this->assertStringNotContainsString('<!-- HYDE[Filepath]', Markdown::render($markdown));
    }

    public function testTerminalExtensionIsAlwaysRegistered()
    {
        $this->assertContains(TerminalExtension::class, $this->markdownService()->getExtensions());

        // Even when the config defines its own extension list
        config(['markdown.extensions' => []]);

        $this->assertContains(TerminalExtension::class, $this->markdownService()->getExtensions());
    }

    public function testHeadingRendererReplacesCommonMarksDefaultHeadingRenderer()
    {
        $environment = $this->readProperty($this->markdownService(), 'converter')->getEnvironment();

        $renderers = iterator_to_array($environment->getRenderersForClass(Heading::class));

        $this->assertInstanceOf(HeadingRenderer::class, $renderers[0]);
    }

    public function testExtensionsListedInTheMarkdownConfigAreRegistered()
    {
        $extensions = $this->markdownService()->getExtensions();

        $this->assertContains(GithubFlavoredMarkdownExtension::class, $extensions);
        $this->assertContains(AttributesExtension::class, $extensions);
    }

    public function testTorchlightExtensionIsRegisteredWhenEnabled()
    {
        $this->assertNotContains(TorchlightExtension::class, $this->markdownService()->getExtensions());

        $service = $this->markdownService(configure: fn (MarkdownService $service) => $service->addFeature('torchlight'));

        $this->assertContains(TorchlightExtension::class, $service->getExtensions());
    }

    public function testDynamicMarkdownLinkProcessorResolvesSourceFileLinksToRoutes()
    {
        Routes::addRoute(new Route(new MarkdownPost('composable-blocks')));

        $this->assertStringContainsString('href="posts/composable-blocks.html"',
            Markdown::render('[My post](_posts/composable-blocks.md)')
        );
    }

    public function testAstBasedBlocksOnlyEverMatchRealMarkdownNodes()
    {
        // A terminal fence inside a Markdown code sample is not a terminal node, so it is left alone
        $html = Markdown::render("````markdown\n```terminal\n\$ php hyde build\n```\n````");

        $this->assertStringContainsString("```terminal\n$ php hyde build\n```", $html);
        $this->assertStringNotContainsString('hyde-terminal', $html);
    }

    public function testStringBasedBlocksWorkOnLinesOfTextAndAreNotFenceAware()
    {
        // A line starting with >info is expanded even inside a fenced code block
        $html = Markdown::render("```php\n>info Hello\n```");

        $this->assertStringContainsString('&lt;blockquote class=&quot;border-blue-500&quot;&gt;', $html);
    }

    /*
    |--------------------------------------------------------------------------
    | Terminal Blocks
    |--------------------------------------------------------------------------
    */

    public function testTheTerminalLanguageRendersAFencedCodeBlockAsATerminalWindow()
    {
        $html = Markdown::render(<<<'MARKDOWN'
        ```terminal
        $ php hyde build

         Building your static site!
         Created 12 files in 0.4 seconds
        ```
        MARKDOWN);

        $this->assertStringContainsString('<figure class="hyde-terminal ', $html);
        $this->assertStringContainsString('<figcaption class="hyde-terminal-header ', $html);
        $this->assertStringContainsString('<pre class="hyde-terminal-body ', $html);
        $this->assertStringContainsString('Building your static site!', $html);
        $this->assertStringContainsString('Created 12 files in 0.4 seconds', $html);
    }

    public function testTerminalBlocksDoNotRequireATorchlightApiToken()
    {
        config(['torchlight.token' => null]);

        $this->assertFalse($this->markdownService()->canEnableTorchlight());

        $html = Markdown::render("```terminal\n\$ php hyde build\n```");

        $this->assertStringContainsString('hyde-terminal', $html);
        $this->assertStringNotContainsString('torchlight', $html);
    }

    public function testTheTerminalViewReceivesASingleFinishedContentsString()
    {
        $this->publishView('vendor/hyde/components/markdown/terminal.blade.php', '[type={{ gettype($contents) }}][contents={!! $contents !!}]');

        // The renderer does the per-line work before the view is involved
        $this->assertStringContainsString('[type=string][contents=Building your static site!',
            Markdown::render("```terminal\nBuilding your static site!\n```")
        );

        $this->assertStringContainsString('[contents=<span class="hyde-terminal-command',
            Markdown::render("```terminal\n\$ php hyde build\n```")
        );
    }

    public function testTerminalContentsAreAlreadyEscapedByTheRendererBeforeTheViewIsInvolved()
    {
        $this->publishView('vendor/hyde/components/markdown/terminal.blade.php', '{!! $contents !!}');

        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', Markdown::render("```terminal\n<script>alert(1)</script>\n```"));
    }

    public function testEchoingTheTerminalContentsEscapedWouldShowTheMarkupAsText()
    {
        // Which is why the shipped view echoes the pre-rendered contents unescaped
        $this->publishView('vendor/hyde/components/markdown/terminal.blade.php', '{{ $contents }}');

        $this->assertStringContainsString('&lt;span class=&quot;hyde-terminal-command',
            Markdown::render("```terminal\n\$ php hyde build\n```")
        );
    }

    public function testTheRendererWrapsPromptLinesInCommandAndPromptSpans()
    {
        $html = Markdown::render("```terminal\n\$ php hyde build\n```");

        $this->assertStringContainsString(
            '<span class="hyde-terminal-command text-[#C3E88D]"><span class="hyde-terminal-prompt select-none" aria-hidden="true">$ </span>php hyde build</span>',
            $html
        );
    }

    public function testTheXmlModifierConvertsSymfonyConsoleFormatterTagsIntoColouredSpans()
    {
        $html = Markdown::render("```terminal xml\n<info>Info</info> <comment>Comment</comment> <question>Question</question> <error>Error</error>\n```");

        $this->assertStringContainsString('<span class="hyde-terminal-info', $html);
        $this->assertStringContainsString('<span class="hyde-terminal-comment', $html);
        $this->assertStringContainsString('<span class="hyde-terminal-question', $html);
        $this->assertStringContainsString('<span class="hyde-terminal-error', $html);

        // Without the modifier the tags are just escaped text
        $this->assertStringContainsString('&lt;info&gt;Info&lt;/info&gt;', Markdown::render("```terminal\n<info>Info</info>\n```"));

        // Everything else is escaped as usual, including unknown tags and tags closed out of order
        $html = Markdown::render("```terminal xml\n<unknown>Text</unknown> <info>Info</comment>\n```");

        $this->assertStringContainsString('&lt;unknown&gt;Text&lt;/unknown&gt;', $html);
        $this->assertStringContainsString('<span class="hyde-terminal-info text-[#C3E88D]">Info&lt;/comment&gt;</span>', $html);
    }

    public function testTheTitleModifierReplacesTheDefaultLabelInTheWindowTitleBar()
    {
        $html = Markdown::render("```terminal title=\"Installing Hyde\"\n\$ composer require hyde/framework\n```");

        $this->assertStringContainsString('<span>Installing Hyde</span>', $html);
        $this->assertStringNotContainsString('<span>Terminal</span>', $html);

        // The label is only replaced for the blocks that set a title
        $this->assertStringContainsString('<span>Terminal</span>', Markdown::render("```terminal\n\$ php hyde build\n```"));
    }

    public function testModifiersAreOrderIndependent()
    {
        foreach (['xml title="Build output"', 'title="Build output" xml'] as $modifiers) {
            $html = Markdown::render("```terminal $modifiers\n<info>Hyde was installed successfully.</info>\n```");

            $this->assertStringContainsString('<span>Build output</span>', $html, "The modifiers [$modifiers] did not set the title.");
            $this->assertStringContainsString('<span class="hyde-terminal-info', $html, "The modifiers [$modifiers] did not enable Symfony formatting.");
        }
    }

    public function testSingleQuotedTitlesAreAcceptedAsAnEquivalentAlternative()
    {
        $this->assertStringContainsString('<span>Build output</span>', Markdown::render("```terminal title='Build output'\nDone!\n```"));

        // Which is useful when the title itself contains a double quote
        $this->assertStringContainsString('<span>The &quot;build&quot; command</span>',
            Markdown::render("```terminal title='The \"build\" command'\nDone!\n```")
        );
    }

    public function testTheTitleIsEscapedWhenItIsRendered()
    {
        $html = Markdown::render("```terminal title=\"<script>alert(1)</script> & 'more'\"\nDone!\n```");

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt; &amp; &#039;more&#039;', $html);
    }

    public function testAnEmptyTitleIsRespectedAsWritten()
    {
        $html = Markdown::render("```terminal title=\"\"\nDone!\n```");

        $this->assertStringContainsString('<span></span>', $html);
        $this->assertStringNotContainsString('<span>Terminal</span>', $html);

        // The window buttons are still there
        $this->assertStringContainsString('<span class="hyde-terminal-controls ', $html);
    }

    public function testAnythingThatReadsAsATitleButIsNotAQuotedAttributeIsRejectedInsteadOfBeingGuessedAt()
    {
        $malformed = ['title=Build', 'title="Build output', "title='Build output", 'title = "Build"', 'title'];

        foreach ($malformed as $modifier) {
            try {
                Markdown::render("```terminal $modifier\nDone!\n```");

                $this->fail("The malformed title [$modifier] was not rejected.");
            } catch (InvalidArgumentException $exception) {
                $this->assertStringContainsString('Invalid terminal block title', $exception->getMessage());
            }
        }

        // While an unknown modifier is still ignored, so that a future one does not break an older renderer
        $this->assertStringContainsString('<span>Terminal</span>', Markdown::render("```terminal future\nDone!\n```"));
        $this->assertStringContainsString('<span>Terminal</span>', Markdown::render("```terminal future=\"yes\"\nDone!\n```"));
    }

    public function testABlockCanOnlyCarryOneTitleAndModifiersHaveToBeSeparatedByWhitespace()
    {
        try {
            Markdown::render("```terminal title=\"One\" title=\"Two\"\nDone!\n```");

            $this->fail('A block with two titles was not rejected.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame('A terminal block can only have one title.', $exception->getMessage());
        }

        try {
            Markdown::render("```terminal title=\"One\"xml\nDone!\n```");

            $this->fail('A title running into the next modifier was not rejected.');
        } catch (InvalidArgumentException $exception) {
            $this->assertStringContainsString('Invalid terminal block title', $exception->getMessage());
        }
    }

    public function testTheTerminalViewReceivesTheTitleAsItWasWrittenOrNull()
    {
        $this->publishView('vendor/hyde/components/markdown/terminal.blade.php', '[title={{ $title }}][type={{ gettype($title) }}]');

        $this->assertStringContainsString('[title=Build output][type=string]', Markdown::render("```terminal title=\"Build output\"\nDone!\n```"));
        $this->assertStringContainsString('[title=][type=NULL]', Markdown::render("```terminal\nDone!\n```"));
    }

    public function testTheShippedViewIsWhatFallsBackToTheDefaultTitle()
    {
        $this->assertStringContainsString("{{ \$title ?? 'Terminal' }}", $this->frameworkViewContents('markdown/terminal.blade.php'));

        // So a view that does not implement the fallback simply renders nothing for an untitled block
        $this->publishView('vendor/hyde/components/markdown/terminal.blade.php', '[title={{ $title }}]');

        $this->assertStringContainsString('[title=]', Markdown::render("```terminal\nDone!\n```"));
    }

    public function testEveryDocumentedTerminalClassHookTargetsTheDocumentedElement()
    {
        $html = Markdown::render("```terminal xml\n\$ php hyde build\n<info>Info</info> <comment>Comment</comment> <question>Question</question> <error>Error</error>\n```");

        $this->assertStringContainsString('<figure class="hyde-terminal ', $html);
        $this->assertStringContainsString('<figcaption class="hyde-terminal-header ', $html);
        $this->assertStringContainsString('<span class="hyde-terminal-controls ', $html);
        $this->assertStringContainsString('<pre class="hyde-terminal-body ', $html);
        $this->assertStringContainsString('<span class="hyde-terminal-command ', $html);
        $this->assertStringContainsString('<span class="hyde-terminal-prompt ', $html);
        $this->assertStringContainsString('<span class="hyde-terminal-info ', $html);
        $this->assertStringContainsString('<span class="hyde-terminal-comment ', $html);
        $this->assertStringContainsString('<span class="hyde-terminal-question ', $html);
        $this->assertStringContainsString('<span class="hyde-terminal-error ', $html);
    }

    public function testTheDocumentedTerminalCustomizationExampleIsTheShippedViewWithADifferentTitle()
    {
        $snippet = $this->documentedSnippet('resources/views/vendor/hyde/components/markdown/terminal.blade.php');

        $this->assertSame(
            trim($this->frameworkViewContents('markdown/terminal.blade.php')),
            str_replace('~/my-project', 'Terminal', $snippet)
        );
    }

    public function testTheDocumentedTerminalCustomizationExampleRendersTheWorkingDirectoryAsTheTitle()
    {
        $this->publishView('vendor/hyde/components/markdown/terminal.blade.php',
            $this->documentedSnippet('resources/views/vendor/hyde/components/markdown/terminal.blade.php')
        );

        $html = Markdown::render("```terminal\n\$ php hyde build\n```");

        $this->assertStringContainsString('<span>~/my-project</span>', $html);
        $this->assertStringNotContainsString('<span>Terminal</span>', $html);
        $this->assertStringContainsString('php hyde build', $html);
    }

    /*
    |--------------------------------------------------------------------------
    | Coloured Blockquotes
    |--------------------------------------------------------------------------
    */

    public function testEveryDocumentedColourKeywordRendersItsDocumentedBorderClass()
    {
        $colors = [
            'info' => 'border-blue-500',
            'success' => 'border-green-500',
            'warning' => 'border-amber-500',
            'danger' => 'border-red-600',
        ];

        foreach ($colors as $color => $class) {
            $html = Markdown::render(">$color The ".$color.' Blockquote');

            $this->assertStringContainsString("<blockquote class=\"$class\">", $html);
            $this->assertStringContainsString("<p>The $color Blockquote</p>", $html);
        }
    }

    public function testANormalBlockquoteIsLeftUntouched()
    {
        $html = Markdown::render('> Normal Blockquote');

        $this->assertStringContainsString('<blockquote>', $html);
        $this->assertStringNotContainsString('border-', $html);
    }

    public function testTheColouredBlockquoteViewReceivesTheColourKeywordAndTheConvertedContents()
    {
        $this->publishView('vendor/hyde/components/colored-blockquote.blade.php', '[class={{ $class }}][type={{ gettype($class) }}][contents={!! $contents !!}]');

        $this->assertStringContainsString('[class=info][type=string][contents=<p>Hello <strong>world</strong></p>]',
            Markdown::render('>info Hello **world**')
        );
    }

    public function testTheShippedColouredBlockquoteViewMatchesTheDocumentedSnippet()
    {
        $this->assertSame(
            trim($this->frameworkViewContents('colored-blockquote.blade.php')),
            $this->documentedSnippet('resources/views/vendor/hyde/components/colored-blockquote.blade.php')
        );
    }

    public function testTheColourKeywordsAreFixedByTheShortcodeSignatures()
    {
        $this->assertSame(['>danger', '>info', '>success', '>warning'], ColoredBlockquotes::getSignatures());

        // So the view can rely on receiving one of the four values
        $this->assertStringNotContainsString('border-', Markdown::render('>purple Not a colour keyword'));
    }

    public function testMarkdownInsideColouredBlockquotesIsConvertedBeforeItReachesTheView()
    {
        $this->assertStringContainsString('<p>Formatting is <strong>supported</strong>!</p>',
            Markdown::render('>info Formatting is **supported**!')
        );
    }

    public function testTheDocumentedColouredBlockquoteCustomizationExampleAddsIconsAndBackgroundTints()
    {
        $this->publishView('vendor/hyde/components/colored-blockquote.blade.php',
            $this->documentedSnippet('resources/views/vendor/hyde/components/colored-blockquote.blade.php', 1)
        );

        $icons = ['info' => 'ℹ️', 'success' => '✅', 'warning' => '⚠️', 'danger' => '⛔'];
        $tints = ['info' => 'bg-blue-500/10', 'success' => 'bg-green-500/10', 'warning' => 'bg-amber-500/10', 'danger' => 'bg-red-600/10'];

        foreach ($icons as $color => $icon) {
            $html = Markdown::render(">$color Hello");

            $this->assertStringContainsString($icon, $html);
            $this->assertStringContainsString($tints[$color], $html);
        }
    }

    public function testColouredBlockquotesAreSingleLineOnly()
    {
        // The shortcode operates on one line at a time, so each line becomes its own blockquote
        $html = Markdown::render(">info Line one\n>info Line two");

        $this->assertSame(2, substr_count($html, '<blockquote class="border-blue-500">'));

        // Inline Markdown works fine
        $this->assertStringContainsString('<strong>bold</strong>', Markdown::render('>info Some **bold** text'));

        // But block-level Markdown does not, as each line is converted on its own
        $html = Markdown::render(">info - One\n>info - Two");

        $this->assertSame(2, substr_count($html, '<ul>'));
        $this->assertSame(2, substr_count($html, '<blockquote class="border-blue-500">'));
    }

    /*
    |--------------------------------------------------------------------------
    | Headings
    |--------------------------------------------------------------------------
    */

    public function testEveryMarkdownHeadingOnEveryPageGoesThroughTheBladeView()
    {
        $this->publishView('vendor/hyde/components/markdown-heading.blade.php', '[heading:{{ $level }}:{!! $slot !!}]');

        $markdown = "# One\n## Two\n### Three\n#### Four\n##### Five\n###### Six";

        foreach ([MarkdownPage::class, MarkdownPost::class, DocumentationPage::class] as $pageClass) {
            $html = Markdown::render($markdown, $pageClass);

            foreach (['1:One', '2:Two', '3:Three', '4:Four', '5:Five', '6:Six'] as $heading) {
                $this->assertStringContainsString("[heading:$heading]", $html);
            }
        }
    }

    public function testTheShippedHeadingViewPowersThePermalinkAnchors()
    {
        $html = Markdown::render('## Hello World', DocumentationPage::class);

        $this->assertStringContainsString('<h2 id="hello-world" class="group w-fit scroll-mt-2">', $html);
        $this->assertStringContainsString('<a href="#hello-world" class="heading-permalink', $html);
    }

    public function testTheHeadingViewReceivesTheDocumentedVariables()
    {
        $this->publishView('vendor/hyde/components/markdown-heading.blade.php',
            '[level={{ $level }}:{{ gettype($level) }}][slot={!! $slot !!}][id={{ $id }}:{{ gettype($id) }}]'
            .'[addPermalink={{ var_export($addPermalink, true) }}:{{ gettype($addPermalink) }}][extraAttributes={!! json_encode($extraAttributes) !!}:{{ gettype($extraAttributes) }}]'
        );

        $html = Markdown::render('## Hello World', DocumentationPage::class);

        $this->assertStringContainsString('[level=2:integer]', $html);
        $this->assertStringContainsString('[slot=Hello World]', $html);
        $this->assertStringContainsString('[id=hello-world:string]', $html);
        $this->assertStringContainsString('[addPermalink=true:boolean]', $html);
        $this->assertStringContainsString('[extraAttributes=[]:array]', $html);

        // The slot is the rendered heading contents as HTML
        $this->assertStringContainsString('[slot=Hello <em>world</em>]', Markdown::render('## Hello *world*'));
    }

    public function testExtraAttributesArePassedFromTheMarkdown()
    {
        $this->publishView('vendor/hyde/components/markdown-heading.blade.php', '[extraAttributes={!! json_encode($extraAttributes) !!}]');

        $this->assertStringContainsString('[extraAttributes={"class":"custom"}]', Markdown::render('## Heading {.custom}'));
    }

    public function testHeadingIdentifiersAreUniqueSlugsDeduplicatedAcrossTheDocument()
    {
        $this->publishView('vendor/hyde/components/markdown-heading.blade.php', '[id={{ $id }}]');

        $html = Markdown::render("## Hello World\n## Hello World\n## Hello World");

        $this->assertStringContainsString('[id=hello-world]', $html);
        $this->assertStringContainsString('[id=hello-world-2]', $html);
        $this->assertStringContainsString('[id=hello-world-3]', $html);
    }

    public function testAddPermalinkIsResolvedFromTheHeadingLevelAndThePermalinkConfigForThePageType()
    {
        $this->publishView('vendor/hyde/components/markdown-heading.blade.php', '[{{ $level }}={{ var_export($addPermalink, true) }}]');

        // Only documentation pages have permalinks by default
        $this->assertStringContainsString('[2=true]', Markdown::render('## Heading', DocumentationPage::class));
        $this->assertStringContainsString('[2=false]', Markdown::render('## Heading', MarkdownPage::class));

        // And only for levels within the configured range
        $this->assertStringContainsString('[1=false]', Markdown::render('# Heading', DocumentationPage::class));

        // The page types are configurable
        config(['markdown.permalinks.pages' => [MarkdownPage::class]]);

        $this->assertStringContainsString('[2=true]', Markdown::render('## Heading', MarkdownPage::class));
    }

    public function testTheHeadingViewIsRenderedWhetherPermalinksAreEnabledOrNot()
    {
        $this->publishView('vendor/hyde/components/markdown-heading.blade.php', '[heading={{ var_export($addPermalink, true) }}]');

        config(['markdown.permalinks.enabled' => false]);

        $this->assertStringContainsString('[heading=false]', Markdown::render('## Heading', DocumentationPage::class));
    }

    public function testTheDocumentedHeadingCustomizationExampleCanUseTheLevelAndPermalinkData()
    {
        $this->publishView('vendor/hyde/components/markdown-heading.blade.php',
            $this->documentedSnippet('resources/views/vendor/hyde/components/markdown-heading.blade.php')
        );

        // The h2 gets the divider rule, and the heading links to itself
        $html = Markdown::render('## Hello World', DocumentationPage::class);

        $this->assertStringContainsString('<h2 id="hello-world" class="border-b pb-2">', $html);
        $this->assertStringContainsString('<a href="#hello-world" class="no-underline">Hello World</a>', $html);

        // Other levels get neither
        $html = Markdown::render('### Hello World', MarkdownPage::class);

        $this->assertStringContainsString('<h3>Hello World</h3>', $html);
    }

    public function testTheHeadingRendererPostProcessesTheRenderedOutput()
    {
        $renderer = new HeadingRenderer();

        // Empty attributes are tidied up, and newlines are collapsed
        $this->assertSame('<h2>Heading</h2>', $renderer->postProcess("<h2 class=\"\">\n    Heading\n</h2>"));
    }

    /*
    |--------------------------------------------------------------------------
    | Code Block Filepath Labels
    |--------------------------------------------------------------------------
    */

    public function testAFilepathCommentOnTheFirstLineOfAFencedCodeBlockBecomesALabel()
    {
        $html = Markdown::render($this->codeBlockWithFilepath());

        $this->assertStringContainsString('<span class="sr-only">Filepath: </span>hello-world.php', $html);
        $this->assertStringContainsString("echo 'Hello World!';", $html);
        $this->assertStringNotContainsString('// filepath:', $html);
    }

    public function testTheFilepathLabelViewReceivesTheDocumentedVariables()
    {
        $this->publishView('vendor/hyde/components/filepath-label.blade.php',
            '[path={{ $path }}][type={{ is_string($path) ? "string" : $path::class }}][torchlight={{ var_export($highlightedByTorchlight, true) }}:{{ gettype($highlightedByTorchlight) }}]'
        );

        $html = Markdown::render($this->codeBlockWithFilepath());

        $this->assertStringContainsString('[path=hello-world.php]', $html);
        $this->assertStringContainsString('[type=Illuminate\Support\HtmlString]', $html);
        $this->assertStringContainsString('[torchlight=false:boolean]', $html);
    }

    public function testThePathIsAPlainStringWhenHtmlIsNotAllowed()
    {
        $this->publishView('vendor/hyde/components/filepath-label.blade.php', '[type={{ is_string($path) ? "string" : $path::class }}]');

        config(['markdown.allow_html' => false]);

        $this->assertStringContainsString('[type=string]', Markdown::render($this->codeBlockWithFilepath()));
    }

    public function testTheLabelCanContainLinksWhenHtmlIsAllowed()
    {
        $markdown = "Intro paragraph.\n\n```php\n// filepath: <a href=\"index.html\">hello-world.php</a>\necho 'Hello World!';\n```";

        $this->assertStringContainsString('<a href="index.html">hello-world.php</a>', Markdown::render($markdown));
    }

    public function testTheShippedFilepathLabelViewHidesTheLabelOnSmallScreens()
    {
        $this->assertStringContainsString('hidden md:block', $this->frameworkViewContents('filepath-label.blade.php'));
    }

    public function testTheLabelIsInjectedIntoTheAlreadyRenderedHtmlAsAPlainFragment()
    {
        $html = Markdown::render($this->codeBlockWithFilepath());

        // The label is a fragment inside the code block, rather than a wrapper around it
        $this->assertStringContainsString('<pre><code class="language-php"><small', $html);
    }

    public function testTheDocumentedFilepathLabelCustomizationExampleShowsTheLabelOnMobile()
    {
        $this->publishView('vendor/hyde/components/filepath-label.blade.php',
            $this->documentedSnippet('resources/views/vendor/hyde/components/filepath-label.blade.php')
        );

        $html = Markdown::render($this->codeBlockWithFilepath());

        $this->assertStringContainsString('<small class="not-prose block opacity-50 hover:opacity-100 md:float-right">', $html);
        $this->assertStringContainsString('<span class="sr-only">Filepath: </span>hello-world.php', $html);
    }

    public function testTorchlightHighlightedCodeBlocksGetTheirOwnLabelPositioning()
    {
        $torchlight = '<!-- Syntax highlighted by torchlight.dev -->';

        $html = CodeblockFilepathProcessor::postprocess(
            "<!-- HYDE[Filepath]hello-world.php -->\n<pre><code class=\"torchlight\">$torchlight<div class=\"line\">Hello</div></code></pre>"
        );

        $this->assertStringContainsString('-top-1 right-1', $html);
        $this->assertStringNotContainsString('top-0 right-0', $html);

        // Without Torchlight the label is positioned differently
        $this->assertStringContainsString('top-0 right-0', Markdown::render($this->codeBlockWithFilepath()));
    }

    /*
    |--------------------------------------------------------------------------
    | Blade Component Blocks
    |--------------------------------------------------------------------------
    */

    public function testTheDocumentedAlertComponentBlockPassesFrontMatterAsAttributesAndTheBodyAsTheSlot()
    {
        $this->publishAlertComponent();

        $html = Markdown::render(<<<'MARKDOWN'
        ```blade component="alert"
        ---
        type: warning
        title: Check this
        ---

        This content is passed to the component **slot**.
        ```
        MARKDOWN);

        // The front matter becomes the component's attribute bag
        $this->assertStringContainsString('border-amber-500', $html);
        $this->assertStringContainsString('<strong>Check this</strong>', $html);

        // The Markdown after it is converted to HTML and passed as the slot
        $this->assertStringContainsString('This content is passed to the component <strong>slot</strong>.', $html);
    }

    public function testEitherPartOfABladeComponentBlockIsOptional()
    {
        $this->publishAlertComponent();

        // Front matter alone
        $html = Markdown::render("```blade component=\"alert\"\n---\ntype: warning\ntitle: Front matter alone\n---\n```");

        $this->assertStringContainsString('<strong>Front matter alone</strong>', $html);
        $this->assertStringContainsString('border-amber-500', $html);

        // Slot content alone
        $html = Markdown::render("```blade component=\"alert\"\nSlot content alone\n```");

        $this->assertStringContainsString('Slot content alone', $html);
        $this->assertStringNotContainsString('border-amber-500', $html);
    }

    public function testBladeComponentBlocksAreGatedBehindTheEnableBladeConfig()
    {
        $this->publishAlertComponent();

        config(['markdown.enable_blade' => false]);

        $html = Markdown::render("```blade component=\"alert\"\nSlot content\n```");

        $this->assertStringNotContainsString('rounded border-l-4 p-4', $html);
        $this->assertStringContainsString('<pre><code class="language-blade">', $html);
    }

    /*
    |--------------------------------------------------------------------------
    | Writing Your Own Composable Block
    |--------------------------------------------------------------------------
    */

    public function testTheDocumentedCalloutBlockRendersThroughItsOwnBladeView()
    {
        $this->registerCalloutExtension();

        $html = Markdown::render("```callout tip\nBlocks you build this way are **composable** in exactly the same way the built-in ones are.\n```");

        $this->assertStringContainsString('<aside class="my-callout my-4 rounded border-l-4 p-4 border-amber-500">', $html);
        $this->assertStringContainsString('are <strong>composable</strong> in', $html);
    }

    public function testTheDocumentedCalloutBlockFallsBackToTheDefaultType()
    {
        $this->registerCalloutExtension();

        $this->assertStringContainsString('border-blue-500', Markdown::render("```callout\nA note.\n```"));
    }

    public function testTheDocumentedCalloutRendererRejectsIncompatibleNodes()
    {
        $this->expectException(InvalidArgumentException::class);

        (new CalloutBlockRenderer())->render(
            Mockery::mock(Node::class),
            Mockery::mock(ChildNodeRendererInterface::class)
        );
    }

    public function testCustomExtensionsAreRegisteredThroughTheMarkdownExtensionsConfig()
    {
        $this->registerCalloutExtension();

        $this->assertContains(CalloutExtension::class, $this->markdownService()->getExtensions());
    }

    public function testCustomCommonMarkExtensionsAreUnaffectedByTheBladeConfig()
    {
        $this->registerCalloutExtension();

        config(['markdown.enable_blade' => false]);

        $this->assertStringContainsString('my-callout', Markdown::render("```callout\nA note.\n```"));
    }

    public function testTheDocumentedWalkthroughCodeIsTheCodeThisTestExercises()
    {
        $classes = [
            'app/Markdown/CalloutBlock.php' => CalloutBlock::class,
            'app/Markdown/TransformCalloutBlocks.php' => TransformCalloutBlocks::class,
            'app/Markdown/CalloutBlockRenderer.php' => CalloutBlockRenderer::class,
            'app/Markdown/CalloutExtension.php' => CalloutExtension::class,
        ];

        foreach ($classes as $documented => $class) {
            $this->assertSame($this->documentedClassDeclaration($documented), $this->classDeclaration($class),
                "The [$documented] example in the documentation does not match the tested implementation."
            );
        }
    }

    public function testANestedMarkdownRenderIsWhatLetsTheCalloutBodyContainMarkdown()
    {
        $this->registerCalloutExtension();

        $this->assertStringContainsString('<p>A <strong>bold</strong> note.</p>', Markdown::render("```callout\nA **bold** note.\n```"));

        // Whereas the terminal renderer escapes its contents and skips the nested render
        $this->assertStringContainsString('**not bold**', Markdown::render("```terminal\n**not bold**\n```"));
    }

    public function testAstBasedExtensionsCannotAccidentallyFireInsideACodeSample()
    {
        $this->registerCalloutExtension();

        $html = Markdown::render("````markdown\n```callout tip\nA note.\n```\n````");

        $this->assertStringNotContainsString('my-callout', $html);
    }

    public function testTheBuiltInBlockViewsOptOutOfTheProseStyles()
    {
        $this->assertStringContainsString('not-prose', $this->frameworkViewContents('markdown/terminal.blade.php'));
        $this->assertStringContainsString('not-prose', $this->frameworkViewContents('filepath-label.blade.php'));
    }

    public function testTheBuiltInBlocksPassDataRatherThanMarkupToTheirViews()
    {
        // The blocks give the view their type, level, or path, rather than a pre-baked class string
        $this->assertSame(['literal', 'usesSymfonyFormatting', 'title'], $this->constructorParameters(TerminalBlock::class));

        $this->publishView('vendor/hyde/components/colored-blockquote.blade.php', '{{ $class }}');
        $this->publishView('vendor/hyde/components/markdown-heading.blade.php', '{{ $level }}');

        $this->assertStringContainsString('info', Markdown::render('>info Hello'));
        $this->assertStringContainsString('2', Markdown::render('## Hello'));
    }

    /*
    |--------------------------------------------------------------------------
    | Limitations and Gotchas
    |--------------------------------------------------------------------------
    */

    public function testTheLeftToRightMarkPreventsShortcodeExpansionWhenDocumentingTheSyntax()
    {
        $html = Markdown::render("\u{200E}>info Info Blockquote");

        $this->assertStringNotContainsString('blockquote', $html);
        $this->assertStringContainsString('&gt;info Info Blockquote', $html);
    }

    public function testTheDocumentationPageUsesTheLeftToRightMarkWorkaroundForItsOwnExamples()
    {
        preg_match_all('/^\x{200E}>(\w+)/mu', $this->documentation(), $matches);

        $this->assertNotEmpty($matches[1], 'The documentation page does not use the documented workaround.');

        // And no unescaped shortcode is left inside a code sample
        foreach ($this->documentationCodeBlocks() as $block) {
            foreach (explode("\n", $block['code']) as $line) {
                foreach (ColoredBlockquotes::getSignatures() as $signature) {
                    $this->assertStringStartsNotWith($signature, $line,
                        "The code sample line [$line] would be expanded by the shortcode processor."
                    );
                }
            }
        }
    }

    public function testCommonMarkExtensionBlocksDoNotHaveTheFenceAwarenessProblem()
    {
        // The terminal fence in this code sample is not expanded, unlike the coloured blockquote above it
        $html = Markdown::render("````markdown\n>info Expanded\n```terminal\n\$ php hyde build\n```\n````");

        $this->assertStringNotContainsString('hyde-terminal', $html);
        $this->assertStringContainsString('&lt;blockquote', $html);
    }

    public function testAPublishedViewDoesNotReceiveFrameworkUpdates()
    {
        ConsoleHelper::mockWindowsOs(true);

        $this->artisan('publish:views components')->assertExitCode(0);

        $published = Hyde::path('resources/views/vendor/hyde/components/colored-blockquote.blade.php');

        // The published file is a copy, frozen at the version it was published from
        $this->assertSame($this->frameworkViewContents('colored-blockquote.blade.php'), $this->normalize(File::get($published)));

        // Once it has been customized, the framework's copy no longer reaches the rendered output
        File::put($published, 'Snapshot: {!! $contents !!}');
        $this->refreshViewFinder();

        $this->assertStringContainsString('Snapshot: <p>Hello</p>', Markdown::render('>info Hello'));
        $this->assertStringContainsString('border-blue-500', $this->frameworkViewContents('colored-blockquote.blade.php'));
    }

    /*
    |--------------------------------------------------------------------------
    | The page's own cross-references
    |--------------------------------------------------------------------------
    */

    public function testEveryInternalAnchorLinkResolvesToAHeadingOnThePage()
    {
        $identifiers = $this->headingIdentifiers($this->documentation());

        preg_match_all('/\]\(#([\w-]+)\)/', $this->documentation(), $anchors);

        $this->assertNotEmpty($anchors[1]);

        foreach ($anchors[1] as $anchor) {
            $this->assertContains($anchor, $identifiers, "The anchor link [#$anchor] does not resolve to a heading on the page.");
        }
    }

    public function testEveryLinkedDocumentationPageExists()
    {
        preg_match_all('/\]\(([\w-]+)(?:#([\w-]+))?\)/', $this->documentation(), $matches, PREG_SET_ORDER);

        $this->assertNotEmpty($matches);

        foreach ($matches as $match) {
            $path = Hyde::path("docs/digging-deeper/$match[1].md");

            $this->assertFileExists($path, "The linked documentation page [$match[1]] does not exist.");

            if (isset($match[2])) {
                $this->assertContains($match[2], $this->headingIdentifiers($this->normalize(File::get($path))),
                    "The link [$match[1]#$match[2]] does not resolve to a heading on the linked page."
                );
            }
        }
    }

    public function testEveryPublishedViewPathMentionedOnThePageIsAccurate()
    {
        preg_match_all('#resources/views/vendor/hyde/components/([\w\-/]+\.blade\.php)#', $this->documentation(), $matches);

        $this->assertNotEmpty($matches[1]);

        foreach ($matches[1] as $view) {
            $this->assertFileExists($this->frameworkView($view), "The documented view path [$view] does not exist in the framework package.");
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /** The block views documented on the page, relative to the components directory. */
    protected function documentedViews(): array
    {
        return [
            'markdown/terminal.blade.php',
            'colored-blockquote.blade.php',
            'markdown-heading.blade.php',
            'filepath-label.blade.php',
        ];
    }

    /** Publish a Blade view into the project, the same way the publish command does. */
    protected function publishView(string $path, string $contents): void
    {
        $this->file("resources/views/$path", $contents);

        $this->refreshViewFinder();
    }

    /**
     * Make the view finder aware of the views published during the test.
     *
     * In a real project the published directory already exists when the view service boots, which is
     * when Laravel adds it to the namespace hints. As we create it mid-test, we do that part here.
     */
    protected function refreshViewFinder(): void
    {
        View::prependNamespace('hyde', Hyde::path('resources/views/vendor/hyde'));
        View::getFinder()->flush();
    }

    /** Create the alert component the documentation uses to demonstrate Blade component blocks. */
    protected function publishAlertComponent(): void
    {
        $this->publishView('components/alert.blade.php', $this->documentedSnippet('resources/views/components/alert.blade.php'));
    }

    /** Create the callout view and register the callout extension, as documented in the walkthrough. */
    protected function registerCalloutExtension(): void
    {
        $this->publishView('components/callout.blade.php', $this->documentedSnippet('resources/views/components/callout.blade.php'));

        config(['markdown.extensions' => [CalloutExtension::class]]);
    }

    /** A Markdown document with a filepath commented code block, as shown in the documentation. */
    protected function codeBlockWithFilepath(): string
    {
        return "Intro paragraph.\n\n```php\n// filepath: hello-world.php\necho 'Hello World!';\n```";
    }

    /** Get a Markdown service instance that has been set up but has nothing to convert. */
    protected function markdownService(?string $pageClass = null, ?callable $configure = null): MarkdownService
    {
        $service = new MarkdownService('', $pageClass);

        if ($configure) {
            $configure($service);
        }

        $service->parse();

        return $service;
    }

    /** Read a protected property, since the Markdown service keeps its wiring internal. */
    protected function readProperty(object $object, string $property): mixed
    {
        return (fn (): mixed => $this->{$property})->call($object);
    }

    /** Get the absolute path to a component view shipped by the framework package. */
    protected function frameworkView(string $view): string
    {
        return Hyde::vendorPath("resources/views/components/$view");
    }

    /** Get the contents of a component view shipped by the framework package. */
    protected function frameworkViewContents(string $view): string
    {
        return $this->normalize(File::get($this->frameworkView($view)));
    }

    /** Get the path the publish command uses to refer to a framework view file. */
    protected function publishableFilePath(string $view): string
    {
        return (File::isDirectory(Hyde::path('packages')) ? 'packages' : 'vendor/hyde')."/framework/resources/views/components/$view";
    }

    /** Get the choice string for the components group in the publish command. */
    protected function componentsGroupChoice(): string
    {
        return '<comment>components</comment>: More or less self contained components, extracted for customizability and DRY code';
    }

    /** Get the contents of the documented page, skipping the test when it is not part of the installation. */
    protected function documentation(): string
    {
        if (! File::exists(Hyde::path(static::PAGE))) {
            $this->markTestSkipped('The documentation page is not part of this installation.');
        }

        return $this->normalize(File::get(Hyde::path(static::PAGE)));
    }

    /**
     * Get all the fenced code blocks in the documented page.
     *
     * @return array<int, array{language: string, code: string}>
     */
    protected function documentationCodeBlocks(): array
    {
        $blocks = [];
        $fence = null;
        $language = '';
        $buffer = [];

        foreach (explode("\n", $this->documentation()) as $line) {
            if ($fence === null) {
                if (preg_match('/^(`{3,})\s*(.*)$/', $line, $matches)) {
                    [$fence, $language, $buffer] = [$matches[1], trim($matches[2]), []];
                }

                continue;
            }

            if (trim($line) === $fence) {
                $blocks[] = ['language' => $language, 'code' => implode("\n", $buffer)];

                $fence = null;

                continue;
            }

            $buffer[] = $line;
        }

        return $blocks;
    }

    /** Get a code block from the documented page by the filepath comment on its first line. */
    protected function documentedSnippet(string $filepath, int $index = 0): string
    {
        $snippets = [];

        foreach ($this->documentationCodeBlocks() as $block) {
            $lines = explode("\n", $block['code']);

            if (str_contains($lines[0] ?? '', "filepath: $filepath")) {
                array_shift($lines);

                $snippets[] = trim(implode("\n", $lines));
            }
        }

        $this->assertArrayHasKey($index, $snippets, "The documentation does not contain snippet [$index] for [$filepath].");

        return $snippets[$index];
    }

    /**
     * Get the permalink identifiers for all the headings in a Markdown document.
     *
     * @return array<int, string>
     */
    protected function headingIdentifiers(string $markdown): array
    {
        preg_match_all('/^#{2,6} (.+)$/m', $markdown, $headings);

        return array_map(fn (string $heading): string => HeadingRenderer::makeIdentifier($heading), $headings[1]);
    }

    /** Get the class declaration from a documented PHP code block, without its namespace and imports. */
    protected function documentedClassDeclaration(string $filepath): string
    {
        return trim(strstr($this->documentedSnippet($filepath), 'class '));
    }

    /** Get the class declaration of a class defined in this test file. */
    protected function classDeclaration(string $class): string
    {
        $reflection = new ReflectionClass($class);

        $lines = explode("\n", $this->normalize(File::get($reflection->getFileName())));

        $declaration = array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1);

        return trim(implode("\n", $declaration));
    }

    /** @return array<int, string> */
    protected function constructorParameters(string $class): array
    {
        return array_map(fn ($parameter): string => $parameter->getName(),
            (new ReflectionClass($class))->getConstructor()->getParameters()
        );
    }

    protected function normalize(string $contents): string
    {
        return str_replace("\r\n", "\n", $contents);
    }
}

/*
|--------------------------------------------------------------------------
| The custom callout block from the "Writing Your Own Composable Block" walkthrough
|--------------------------------------------------------------------------
|
| These classes are copied verbatim from the documentation, apart from their namespace, which
| the test above asserts, so that the walkthrough is proven to work exactly as it is written.
|
*/

class CalloutBlock extends AbstractBlock
{
    public function __construct(
        public readonly string $literal,
        public readonly string $type = 'note',
    ) {
        parent::__construct();
    }
}

class TransformCalloutBlocks
{
    public function __invoke(DocumentParsedEvent $event): void
    {
        $matches = [];

        foreach ($event->getDocument()->iterator() as $node) {
            if ($node instanceof FencedCode && strtolower($node->getInfoWords()[0] ?? '') === 'callout') {
                $matches[] = $node;
            }
        }

        // Collect first, then replace, so we don't mutate the tree while iterating it
        foreach ($matches as $node) {
            $node->replaceWith(new CalloutBlock(
                $node->getLiteral(),
                strtolower($node->getInfoWords()[1] ?? 'note'),
            ));
        }
    }
}

class CalloutBlockRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        if (! $node instanceof CalloutBlock) {
            throw new \InvalidArgumentException('Incompatible node type: '.$node::class);
        }

        return view('components.callout', [
            'type' => $node->type,
            'contents' => Markdown::render($node->literal),
        ])->render();
    }
}

class CalloutExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment
            ->addEventListener(DocumentParsedEvent::class, new TransformCalloutBlocks(), 100)
            ->addRenderer(CalloutBlock::class, new CalloutBlockRenderer());
    }
}

abstract class PromptFallbackReset extends Prompt
{
    // Workaround for https://github.com/laravel/prompts/issues/158
    public static function resetFallbacks(): void
    {
        static::$shouldFallback = false;
    }
}
