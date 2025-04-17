package com.example.poleinfo

data class Reservation(
    val id_reservation: Int,
    val duree: Double,
    val date: String,
    val info: String,
    val numero_salle: String,
    val capacite_salle: Int,
    val type_salle: String,
    val nom_matiere: String,
    val heure_debut: String,
    val nom_user: String,
    val prenom: String,
    val noms_classes: String
)
