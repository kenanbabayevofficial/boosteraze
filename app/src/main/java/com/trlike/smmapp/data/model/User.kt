package com.trlike.smmapp.data.model

data class User(
    val id: String,
    val email: String,
    val name: String?,
    val photoUrl: String?,
    val credits: Int,
    val isBanned: Boolean = false,
    val createdAt: String
)