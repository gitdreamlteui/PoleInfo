package com.example.poleinfo

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.TextView
import androidx.recyclerview.widget.RecyclerView
import java.text.SimpleDateFormat
import java.time.LocalTime
import java.time.format.DateTimeFormatter
import java.util.*

class ReservationAdapter(
    private val reservations: List<Reservation>,
    private val listener: OnReservationClickListener
) : RecyclerView.Adapter<ReservationAdapter.ReservationViewHolder>() {

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ReservationViewHolder {
        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_reservation, parent, false)
        return ReservationViewHolder(view)
    }

    override fun onBindViewHolder(holder: ReservationViewHolder, position: Int) {
        val reservation = reservations[position]
        val localeFR = Locale("fr", "FR")

        // Formatage de la date en français
        val inputDate = SimpleDateFormat("yyyy-MM-dd", Locale.US).parse(reservation.date)
        val formattedDate = SimpleDateFormat("d MMMM yyyy", localeFR).format(inputDate!!)

        // Heure de début : conversion de "PT8H10M" en "8h10"
        val heureRegex = Regex("""PT(\d+)H(\d+)?M?""")
        val match = heureRegex.matchEntire(reservation.heure_debut)
        val heure = match?.groupValues?.getOrNull(1)?.toIntOrNull() ?: 0
        val minute = match?.groupValues?.getOrNull(2)?.toIntOrNull() ?: 0
        val heureDebutText = String.format("%02dh%02d", heure, minute)

        // Calcul de l’heure de fin
        val dureeEnMinutes = Math.round(reservation.duree * 60).toLong() // Arrondir la durée en minutes
        val debut = LocalTime.of(heure, minute)
        val fin = debut.plusMinutes(dureeEnMinutes)
        val heureFinText = fin.format(DateTimeFormatter.ofPattern("HH'h'mm"))

        holder.tvDate.text = formattedDate
        holder.tvHeureDebut.text = "Début : $heureDebutText - Fin : $heureFinText"
        holder.tvMatiere.text = "Matière : ${reservation.nom_matiere}"
        holder.tvSalle.text = "Salle : ${reservation.numero_salle}"
        holder.tvProfesseur.text = "Professeur : ${reservation.prenom} ${reservation.nom_user.toUpperCase()}"
        holder.tvInfo.text = "Info : ${reservation.info}"
    }

    override fun getItemCount(): Int = reservations.size

    inner class ReservationViewHolder(itemView: View) : RecyclerView.ViewHolder(itemView) {
        val tvDate: TextView = itemView.findViewById(R.id.tvDate)
        val tvHeureDebut: TextView = itemView.findViewById(R.id.tvHeureDebut)
        val tvMatiere: TextView = itemView.findViewById(R.id.tvMatiere)
        val tvSalle: TextView = itemView.findViewById(R.id.tvSalle)
        val tvProfesseur: TextView = itemView.findViewById(R.id.tvProfesseur)
        val tvInfo: TextView = itemView.findViewById(R.id.tvInfo)

        init {
            // Ajout d’un écouteur de clic sur chaque élément
            itemView.setOnClickListener {
                val position = adapterPosition
                if (position != RecyclerView.NO_POSITION) {
                    listener.onReservationClicked(reservations[position])
                }
            }
        }
    }
}

// Interface pour gérer les clics sur les réservations
interface OnReservationClickListener {
    fun onReservationClicked(reservation: Reservation)
}