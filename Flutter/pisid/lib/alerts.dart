import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:http/http.dart' as http;

import 'dart:convert';
import 'dart:async';
import './readings.dart';

class Alerts extends StatelessWidget {
  const Alerts({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    const appTitle = 'Alerts';

    return Scaffold(
      appBar: AppBar(
        centerTitle: true,
        title: const Text(appTitle),
      ),
      body: const AlertsMain(),
    );
  }
}

class AlertsMain extends StatefulWidget {
  const AlertsMain({Key? key}) : super(key: key);

  @override
  AlertsMainState createState() {
    return AlertsMainState();
  }
}

class AlertsMainState extends State<AlertsMain> {
  late Timer timer;
  DateTime selectedDate = DateTime.now();
  var mostRecentAlert = 0;

  var tableFields = ['Zona', 'Sensor', 'Hora', 'Leitura', 'NivelAlerta', 'NomeCultura', 'Mensagem'];
  var tableAlerts = <int, List<String>>{};

  @override
  void initState() {
    const oneSec = Duration(seconds:1);
    timer = Timer.periodic(oneSec, (Timer t) => getAlerts());
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
        body: SingleChildScrollView(
          scrollDirection: Axis.vertical,
          child: Column(
            children: <Widget>[
              ElevatedButton(
                onPressed: () {
                  selectDate(context);
                },
                child: const Text("Choose Date"),
              ),
              Text(
                  "${selectedDate.day}/${selectedDate.month}/${selectedDate.year}"),
              SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: DataTable(
                  columns: listFields(),
                  rows: listAlerts(),
                ),
              ),
            ],
          ),
        ),
        bottomNavigationBar: BottomAppBar(
          child: ElevatedButton(
            onPressed: () {

              //mostRecentAlert = 0;
              //tableAlerts.clear();

              //final prefs = await SharedPreferences.getInstance();
              //prefs.remove('alertDataHora');

              Navigator.push(
                context,
                MaterialPageRoute(builder: (context) => const Readings()),
              );
            },
            child: const Text('Readings'),
          ),
        ));
  }

  selectDate(BuildContext context) async {
    final DateTime? selected = await showDatePicker(
      context: context,
      initialDate: selectedDate,
      firstDate: DateTime(selectedDate.year - 2),
      lastDate: DateTime(selectedDate.year + 2),
    );
    final prefs = await SharedPreferences.getInstance();
    if (selected != null && selected != selectedDate) {
      setState(() {
        if(selectedDate != selected){
          tableAlerts.clear();
          prefs.remove('alertDataHora');
        }
        selectedDate = selected;

      });
      getAlerts();
    }
  }

  getAlerts() async {
    final prefs = await SharedPreferences.getInstance();
    String? username = prefs.getString('username');
    String? password = prefs.getString('password');
    String? ip = prefs.getString('ip');
    String? port = prefs.getString('port');
    String? alertDataHora = prefs.getString('alertDataHora');
    String date = "${selectedDate.year}-${selectedDate.month}-${selectedDate.day}";

    print('alertDataHora++++');
    print(alertDataHora);
    print('alertDataHora----');

    String alertsURL = "http://" + ip! + ":" + port! + "/scripts/getAlerts.php";
    var response = await http
        .post(Uri.parse(alertsURL), body: {'username': username, 'password':password, 'date': date, 'fromDateTime':(alertDataHora ?? "")});

    if (response.statusCode == 200) {
      var jsonData = json.decode(response.body);

      var alerts = jsonData["alerts"];
      print("alertas");

      if (alerts != null && alerts.length > 0) {
        var maxTimeKey = 0;
        var maxDateTime = "";
        setState(() {
          //tableAlerts.clear();
          for (var i = 0; i < alerts.length; i++) {
            Map<String, dynamic> alert = alerts[i];
            //print(alert["Hora"].toString().replaceAll(RegExp(r"[^0-9]"), ""));
            int timeKey = int.parse(alert["Hora"].toString().replaceAll(RegExp(r"[^0-9]"), ""));
            //int timeKey = int.parse(alert["Hora"].toString().split(" ")[1].replaceAll(":", ""));
            if(tableAlerts[timeKey]==null) {
              print("novo alerta:"+timeKey.toString());
              var alertValues = <String>[];
              for (var key in alert.keys) {
                if (alert[key] == null) {
                  alertValues.add("");
                } else {
                  alertValues.add(alert[key]);
                }
              }
              tableAlerts[timeKey] = alertValues;
              if(maxTimeKey<timeKey){
                maxTimeKey = timeKey;
                maxDateTime = alert["Hora"].toString();
              }
            }

          }
        });
        await prefs.setString('alertDataHora', maxDateTime);
      }
    }
  }

  listAlerts() {
    var alertsList = <DataRow>[];
    if (tableAlerts.isEmpty) return alertsList;
    for (var i = tableAlerts.length - 1; i >= 0; i--) {
      var key = tableAlerts.keys.elementAt(i);
      var alertRow = <DataCell>[];
      tableAlerts[key]?.forEach((alertField) {
        if (key>mostRecentAlert) {
          alertRow.add(DataCell(Text(alertField, style: const TextStyle(color: Colors.blue))));
        } else {
          alertRow.add(DataCell(Text(alertField)));
        }
      });
      alertsList.add(DataRow(cells: alertRow));
    }
    mostRecentAlert = tableAlerts.keys.elementAt(tableAlerts.length-1);
    return alertsList;
  }

  listFields() {
    var fields = <DataColumn>[];
    for (var field in tableFields) {
      fields.add(DataColumn(label: Text(field)));
    }
    return fields;
  }

  @override
  void dispose() {
    timer.cancel();
    super.dispose();
  }

}
