
import helper from "./helper";
import Chart from "chart.js/auto";
import { bottom } from "@popperjs/core";
import colors from "./colors";

(function(){

    let attendanceTrendLineChart = null;
    $(window).on('load', function(){
        let labels = ['1st Week', '2nd Week', '3rd Week', '4th Week', '5th Week', '6th Week', '7th Week', '8th Week', '9th Week', '10th Week', '11th Week'];

        let ctx = document.getElementById('attendanceTrendLineChart').getContext("2d");
        attendanceTrendLineChart = new Chart(ctx, {
            type: "line",
            data: {
                labels : labels,
                datasets : [
                    {
                        label: "Overall",
                        data: [
                            20, 200, 250, 200, 700, 550, 650, 1050, 950, 1100,
                            900
                        ],
                        borderWidth: 3,
                        borderColor: colors.primary(0.8),
                        backgroundColor: "transparent",
                        pointBorderColor: "transparent",
                        tension: 0.4,
                    },
                    {
                        label: "HND IN HOSPITALITY MANAGEMENT",
                        data: [
                            0, 300, 400, 560, 320, 600, 720, 850, 690, 805,
                            1200
                        ],
                        borderWidth: 3,
                        borderColor: colors.success(0.8),
                        backgroundColor: "transparent",
                        pointBorderColor: "transparent",
                        tension: 0.4,
                    },
                ]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
            }
        });
    });
})()