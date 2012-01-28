# Gorilla
A combined command-line and web-based replacement for the Atom Protocol
Exerciser (APE).

## Usage
The basic usage of Gorilla is passing a AtomPub base URL to `bin/gorilla`:

	bin/gorilla --uri=http://example.com/wp-app.php

This will run all the AtomPub tests, using `http://example.com/wp-app.php` as
the base.

Individual tests can be specified by passing them in:

	bin/gorilla --uri=http://example.com/wp-app.php ServiceDocumentTest

User and password for Basic authentication can be passed in with the `user` and
`pass` parameters:

	bin/gorilla --user=admin --pass=password --uri=http://example.com/wp-app.php

For debugging purposes, `--trace` can be specified to also print a backtrace
where possible.

## License
Copyright (c) 2011-2012 Ryan McCue

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
