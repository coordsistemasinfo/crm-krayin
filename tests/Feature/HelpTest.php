<?php

it('shows the help page to an authenticated admin', function () {
    $admin = getDefaultAdmin();

    test()->actingAs($admin)
        ->get(route('admin.help.index'))
        ->assertOk()
        ->assertSee(trans('admin::app.help.index.title'))
        ->assertSee(trans('admin::app.help.index.services.cloud-hosting.title'))
        ->assertSee(trans('admin::app.help.index.services.extensions.title'))
        ->assertSee('krayincrm.com/cloud-hosting')
        ->assertSee(trans('admin::app.help.index.still-need-help-title'))
        ->assertSee(trans('admin::app.help.index.community.forums.title'))
        ->assertSee(trans('admin::app.help.index.community.tutorials.title'));
});
