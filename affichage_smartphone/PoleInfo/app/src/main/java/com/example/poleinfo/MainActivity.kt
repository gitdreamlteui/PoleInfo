package com.example.poleinfo

import android.content.Intent
import android.graphics.Rect
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.provider.CalendarContract
import android.view.View
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.android.volley.Request
import com.android.volley.Response
import com.android.volley.toolbox.JsonArrayRequest
import com.android.volley.toolbox.Volley
import org.json.JSONArray
import java.text.SimpleDateFormat
import java.util.*
import kotlin.concurrent.fixedRateTimer

class MainActivity : AppCompatActivity(), OnReservationClickListener {

    private lateinit var recyclerView: RecyclerView
    private lateinit var errorText: TextView
    private lateinit var reservationAdapter: ReservationAdapter
    private var reservationList: MutableList<Reservation> = mutableListOf()
    private val handler = Handler(Looper.getMainLooper())
    private var hasFetchedData = false // Indicateur pour vérifier si des données ont été récupérées

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)

        // Supprimer l'ombre de l'ActionBar pour réduire l'espace visuel
        supportActionBar?. elevation = 0f

        // Initialisation du RecyclerView
        recyclerView = findViewById(R.id.recyclerView)
        recyclerView.layoutManager = LinearLayoutManager(this)

        // Initialisation du TextView pour l'erreur
        errorText = findViewById(R.id.connectionErrorText)
        errorText.visibility = View.GONE // Caché par défaut

        // Ajout d'un ItemDecoration pour gérer l'espacement entre les éléments
        recyclerView.addItemDecoration(object : RecyclerView.ItemDecoration() {
            override fun getItemOffsets(
                outRect: Rect,
                view: View,
                parent: RecyclerView,
                state: RecyclerView.State
            ) {
                val position = parent.getChildAdapterPosition(view)
                // Ajouter un espace de 8dp entre les éléments, mais pas au-dessus du premier
                if (position != 0) {
                    outRect.top = (8 * resources.displayMetrics.density).toInt() // 8dp converti en pixels
                }
            }
        })

        // Initialisation de l’adaptateur avec le listener
        reservationAdapter = ReservationAdapter(reservationList, this)
        recyclerView.adapter = reservationAdapter

        // Récupération des réservations via l'API
        fetchReservations()

        // Mise à jour automatique toutes les 10 secondes
        fixedRateTimer("refreshTimer", false, 0L, 10_000L) {
            handler.post {
                fetchReservations()
            }
        }
    }

    // Méthode pour récupérer les réservations depuis l’API
    private fun fetchReservations() {
        val apiUrl = "http://192.168.8.152:8000/reservations/"
        val jsonRequest = JsonArrayRequest(Request.Method.GET, apiUrl, null,
            Response.Listener { response ->
                // Succès : mettre à jour les données
                val newList = parseReservations(response)
                if (newList != reservationList) {
                    reservationList.clear()
                    reservationList.addAll(newList)
                    reservationAdapter.notifyDataSetChanged()
                }
                hasFetchedData = true // Marquer que des données ont été récupérées
                recyclerView.visibility = View.VISIBLE // Afficher le RecyclerView
                errorText.visibility = View.GONE // Cacher le message d'erreur
            },
            Response.ErrorListener { error ->
                // Échec : gérer l'affichage en fonction de l'état
                if (!hasFetchedData) {
                    // Afficher le message d'erreur si aucune donnée n'a été récupérée
                    errorText.visibility = View.VISIBLE
                    recyclerView.visibility = View.GONE
                } else {
                    // Si des données existent, montrer un Toast mais garder la liste
                    Toast.makeText(this, "Erreur de connexion : ${error.message}", Toast.LENGTH_SHORT).show()
                }
            })

        val requestQueue = Volley.newRequestQueue(this)
        requestQueue.add(jsonRequest)
    }

    // Méthode pour parser les données JSON des réservations
    private fun parseReservations(response: JSONArray): List<Reservation> {
        val list = mutableListOf<Reservation>()
        for (i in 0 until response.length()) {
            val jsonObject = response.getJSONObject(i)
            val reservation = Reservation(
                jsonObject.getInt("id_reservation"),
                jsonObject.getDouble("duree"),
                jsonObject.getString("date"),
                jsonObject.getString("info"),
                jsonObject.getString("numero_salle"),
                jsonObject.getInt("capacite_salle"),
                jsonObject.getString("type_salle"),
                jsonObject.getString("nom_matiere"),
                jsonObject.getString("heure_debut"),
                jsonObject.getString("nom_user"),
                jsonObject.getString("prenom"),
                jsonObject.getString("noms_classes")
            )
            list.add(reservation)
        }
        return list.sortedWith(compareBy({ it.date }, { it.heure_debut }))
    }

    // Gestion du clic sur une réservation
    override fun onReservationClicked(reservation: Reservation) {
        // Création d’une intention pour ajouter un événement dans le calendrier
        val intent = Intent(Intent.ACTION_INSERT)
            .setDataAndType(CalendarContract.Events.CONTENT_URI, "vnd.android.cursor.dir/event")
            .addCategory(Intent.CATEGORY_DEFAULT)
            .putExtra(CalendarContract.Events.TITLE, reservation.nom_matiere)
            .putExtra(CalendarContract.Events.EVENT_LOCATION, reservation.numero_salle)
            .putExtra(CalendarContract.Events.DESCRIPTION, reservation.info)

        try {
            val dateFormat = SimpleDateFormat("yyyy-MM-dd", Locale.US)
            val date = dateFormat.parse(reservation.date)
            val calendar = Calendar.getInstance()
            calendar.time = date

            val match = Regex("""PT(\d+)H(\d+)?M?""").matchEntire(reservation.heure_debut)
            val hour = match?.groupValues?.getOrNull(1)?.toIntOrNull() ?: 0
            val minute = match?.groupValues?.getOrNull(2)?.toIntOrNull() ?: 0

            calendar.set(Calendar.HOUR_OF_DAY, hour)
            calendar.set(Calendar.MINUTE, minute)
            calendar.set(Calendar.SECOND, 0)

            val startMillis = calendar.timeInMillis
            val durationMinutes = (reservation.duree * 60).toLong()
            val endMillis = startMillis + durationMinutes * 60 * 1000

            intent.putExtra(CalendarContract.EXTRA_EVENT_BEGIN_TIME, startMillis)
            intent.putExtra(CalendarContract.EXTRA_EVENT_END_TIME, endMillis)

            val activities = packageManager.queryIntentActivities(intent, 0)
            if (activities.isNotEmpty()) {
                startActivity(intent)
            } else {
                Toast.makeText(this, "Aucune application de calendrier trouvée", Toast.LENGTH_SHORT).show()
            }
        } catch (e: Exception) {
            Toast.makeText(this, "Erreur lors de la création de l’événement", Toast.LENGTH_SHORT).show()
        }
    }
}