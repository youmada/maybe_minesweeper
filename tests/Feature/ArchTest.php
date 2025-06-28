<?php

it('finds mistakes debug statement in the code', function () {
    expect(['dd', 'dump', 'ray', 'var_dump'])->not->tobeUsed();
});
