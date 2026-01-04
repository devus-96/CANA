<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->json('permissions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles');
    }
};

/*
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Méthode à exécuter dans un seeder ou une migration

DB::table('roles')->insert([
    [
        'name' => 'Administrateur',
        'description' => 'Gestion complète du site : blog, méditations, médias, états de vie, événements, réservations et utilisateurs',
        'permissions' => json_encode([
            'blog.create', 'blog.edit', 'blog.delete', 'blog.publish',
            'meditations.create', 'meditations.edit', 'meditations.delete', 'meditations.schedule',
            'media.upload', 'media.edit', 'media.delete', 'media.manage',
            'etats_vie.manage', 'etats_vie.assign_responsables',
            'events.create', 'events.edit', 'events.delete', 'events.publish',
            'reservations.view', 'reservations.manage', 'reservations.export',
            'payments.view', 'payments.manage',
            'donations.view', 'donations.export',
            'users.create', 'users.edit', 'users.delete', 'users.assign_roles',
            'activities.create', 'activities.edit', 'activities.delete',
            'projects.create', 'projects.edit', 'projects.delete',
            'settings.manage', 'site.manage'
        ]),
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ],
    [
        'name' => 'Responsable État de Vie',
        'description' => 'Gestion des contenus spécifiques à un groupe (jeunes, couples, laïcs, etc.)',
        'permissions' => json_encode([
            'etats_vie.publish_content',
            'etats_vie.manage_group_info',
            'etats_vie.publish_messages',
            'etats_vie.publish_teachings',
            'etats_vie.upload_resources',
            'etats_vie.view_group_news',
            'media.upload' // limité à leur groupe
        ]),
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ],
    [
        'name' => 'Utilisateur / Visiteur',
        'description' => 'Consultation publique et réservation d\'événements',
        'permissions' => json_encode([
            'site.view',
            'blog.read',
            'meditations.read',
            'media.view',
            'events.view',
            'reservations.create',
            'reservations.view_own',
            'payments.make',
            'donations.make',
            'contact.send',
            'etats_vie.view'
        ]),
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ],
]);*/
