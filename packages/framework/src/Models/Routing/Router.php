<?php

namespace Hyde\Framework\Models\Routing;

/**
 * @experimental Pseudo-Router for Hyde
 *
 * This is not a router in the traditional sense that it decides where to go.
 * Instead, it creates a pre-generated object encapsulating the Hyde autodiscovery.
 *
 * If successful, this will not only let us emulate Laravel route helpers, but also
 * serve as the canonical source of truth for the Hyde autodiscovery process.
 *
 * The routes defined can then also be used to power the RealtimeCompiler without
 * having to reverse-engineer the source file mapping.
 *
 * Routes cannot be added manually, instead the route index is created using the
 * exact same rules as the current autodiscovery process and compiled file output.
 *
 * The route index shall serve as a multidimensional mapping allowing you to
 * determine where a source file will be compiled to, and where a compiled
 * file was generated from.
 */
class Router
{
    // Create the magic here.
}
